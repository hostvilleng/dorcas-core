<?php

namespace App\Http\Controllers\Coupons;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\User;
use App\Transformers\CouponTransformer;
use App\Transformers\CouponUsageTransformer;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Coupons extends Controller
{
    /**
     * Determines the column to use in finding the coupon.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineSelectColumn(Request $request): string
    {
        $allowedColumns = ['uuid', 'code'];
        $column = strtolower((string) $request->input('select_using', $allowedColumns[0]));
        return in_array($column, $allowedColumns) ? $column : $allowedColumns[0];
    }
    
    /**
     * @param int $length
     *
     * @return string
     */
    public static function generateCode(int $length = 10): string
    {
        $byteLength = $length % 2 === 0 ? $length / 2 : (int) ceil($length / 2);
        return strtoupper(bin2hex(random_bytes($byteLength)));
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCoupons(Request $request, Manager $fractal)
    {
        $this->validate($request, [
            'plan' => 'nullable|string',
            'user' => 'nullable|string',
            'type' => 'required|string|in:upgrade,regular',
            'currency' => 'required_with:amount|string|size:3',
            'amount' => 'required_if:type,regular',
            'max_usages' => 'nullable|numeric',
            'description' => 'nullable|string',
            'extra_data' => 'nullable|array',
            'count' => 'nullable|numeric',
            'code_length' => 'nullable|numeric',
            'expires_at' => 'nullable|date_format:d/m/Y',
            'expires_in' => 'nullable|string',
        ]);
        # validate the request
        $type = $request->input('type');
        $codeLength = (int) $request->input('code_length', 10);
        $count = abs((int) $request->input('count', 1));
        $maxUsages = (int) $request->input('max_usages', 0);
        $currency = $request->input('currency', 'NGN');
        $expiry = null;
        if ($request->has('expires_in')) {
            $expiry = Carbon::parse($request->input('expires_in'));
        } elseif ($request->has('expires_at')) {
            $expiry = Carbon::createFromFormat('d/m/Y', $request->input('expires_at'))->startOfDay();
        }
        # get the values
        $coupons = collect([]);
        # container for the generated coupons
        $plan = null;
        if ($request->has('plan')) {
            $plan = Plan::where('uuid', $request->input('plan'))->first();
            if (empty($plan)) {
                throw new RecordNotFoundException('Could not find the specified plan for the coupon(s)');
            }
            $type = 'upgrade';
        }
        $user = null;
        if ($request->has('user')) {
            $user = User::where('uuid', $request->input('user'))->first();
            if (empty($user)) {
                throw new RecordNotFoundException('Could not find the user for the coupon');
            }
            $count = 1;
        }
        $couponData = [
            'type' => $type,
            'plan_id' => !empty($plan) ? $plan->id : null,
            'user_id' => !empty($user) ? $user->id : null,
            'currency' => $currency,
            'amount' => $request->input('amount', 0),
            'max_usages' => $maxUsages,
            'description' => $request->input('description'),
            'extra_data' => $request->input('extra_data'),
            'expires_at' => !empty($expiry) ? $expiry->format('Y-m-d H:i:s') : null
        ];
        if ($count === 1) {
            $coupon = Coupon::create(array_merge($couponData, ['code' => self::generateCode($codeLength)]));
            # create the coupon
            $coupons->push($coupon);
            # add it to the coupons list
        } else {
            for ($i = 1; $i <= $count; $i++) {
                $coupon = Coupon::create(array_merge($couponData, ['code' => self::generateCode($codeLength)]));
                # create the coupon
                $coupons->push($coupon);
                # add it to the coupons list
            }
        }
        $resource = new Collection($coupons, new CouponTransformer(), 'coupon');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $column = $this->determineSelectColumn($request);
        # set the column to find by
        $coupon = Coupon::withCount('usages')->where($column, $id)->firstOrFail();
        # get the coupon
        $resource = new Item($coupon, new CouponTransformer(), 'coupon');
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function redeem(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'action' => 'nullable|string|in:upgrade,regular'
        ]);
        # validate the request
        $action = $request->input('action', 'upgrade');
        # set the redeem action
        $column = $this->determineSelectColumn($request);
        # set the column to find by
        $coupon = Coupon::withCount('usages')->where($column, $id)->first();
        # get the coupon
        if (empty($coupon)) {
            throw new RecordNotFoundException('Could not find the coupon code.');
        }
        if (!empty($coupon->expires_at) && Carbon::now()->greaterThan($coupon->expires_at)) {
            # coupon has an expiry date, and it has passed
            throw new \UnexpectedValueException('The coupon expired on '.$coupon->expires_at->format('D jS M, Y'));
        }
        if ($action === 'upgrade' && $coupon->type !== $action) {
            # requesting an upgrade action on the wring coupon
            throw new \UnexpectedValueException('The coupon you provided can not be used for a plan upgrade.');
        } elseif ($coupon->type !== $action) {
            throw new \UnexpectedValueException('The coupon cannot be used for this purpose.');
        }
        if ($coupon->max_usages > 0 && $coupon->usages_count >= $coupon->max_usages) {
            throw new \UnexpectedValueException(
                'This coupon has expired (It has been used for the maximum number of times)'
            );
        }
        if (!empty($coupon->user_id) && $coupon->user_id !== $request->user()->id) {
            # check the user
            throw new AuthorizationException('This coupon does not belong to your account.');
        }
        $couponUsage = $coupon->usages()->where('user_id', $request->user()->id)->first();
        # get the coupon usage
        if (!empty($couponUsage)) {
            # coupon has already been redeemed by the user
            throw new \UnexpectedValueException('You have already previously redeemed this coupon.');
        }
        $messages = [];
        # messages on the action
        DB::transaction(function () use (&$couponUsage, $coupon, &$messages, $request) {
            $couponUsage = $coupon->usages()->create([
                'user_id' => $request->user()->id
            ]);
            # add the usage for the user
            if ($coupon->type === 'upgrade') {
                # perform the account upgrade for the user
                $configurations = (array) $coupon->extra_data;
                $periodCount = $configurations['period_count'] ?? 1;
                $periodType = $configurations['period_type'] ?? 'months';
                $periodType = !in_array($periodType, ['months', 'years']) ? 'months' : $periodType;
                # get the extension period
                $company = $request->user()->company;
                # get the user's company
                $plan = $company->plan;
                # get the plan
                if (empty($plan)) {
                    throw new \UnexpectedValueException('We could not find your current account pricing plan.');
                }
                $couponPlan = $coupon->plan;
                # get the plan on the coupon
                if ($couponPlan->id === $plan->id) {
                    # checking that the coupon plan applies
                    $messages[] = 'The coupon is on the same plan as your current plan, an extension will be performed.';
                } else {
                    $company->plan_id = $couponPlan->id;
                    $company->plan_type = 'monthly';
                }
                if ($couponPlan->price_monthly < $plan->price_monthly && !$request->has('allow_downgrade')) {
                    throw new \UnexpectedValueException(
                        'The coupon is on a lower tier plan than your current; you need to enable the '.
                        'downgrade option to confirm the downgrade.'
                    );
                }
                $transaction = $company->billPayments()->firstOrNew([
                    'reference' => 'coupon-' . $coupon->code,
                    'processor' => 'upgrade-coupon'
                ]);
                # get the transaction object
                $unitPrice = $periodType === 'months' ? $couponPlan->price_monthly : $couponPlan->price_yearly;
                # the unit price
                $transaction->plan_id = $couponPlan->id;
                $transaction->currency = 'NGN';
                $transaction->amount = $unitPrice * $periodCount;
                $transaction->json_data = ['coupon' => $coupon->toArray()];
                $transaction->is_successful = 1;
                # set the details on the transaction
                $transaction->saveOrFail();
                # save the transaction
                $currentExpiry = empty($company->access_expires_at) || Carbon::now()->greaterThan($company->access_expires_at) ?
                    Carbon::now() : $company->access_expires_at;
                # set the current expiry
                if ($periodType === 'months') {
                    $company->access_expires_at = $currentExpiry->addMonths($periodCount)->endOfDay();
                } else {
                    $company->access_expires_at = $currentExpiry->addYears($periodCount)->endOfDay();
                }
                # set the properties
                $company->saveOrFail();
                # save changes to the company
            } else {
                # just redeem the value -- probably to a wallet
            }
        });
        # perform actions in a transaction
        $resource = new Item($couponUsage, new CouponUsageTransformer(), 'coupon_usage');
        $resource->setMetaValue('messages', $messages);
        return response()->json($fractal->createData($resource)->toArray());
    }
}