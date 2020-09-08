<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Coupon;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class CouponTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['plan', 'user', 'usages'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['plan'];
    
    /**
     * @param Coupon $coupon
     *
     * @return array
     */
    public function transform(Coupon $coupon)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $coupon->uuid,
            'type' => $coupon->type,
            'code' => $coupon->code,
            'currency' => $coupon->currency,
            'amount' => [
                'raw' => $coupon->amount,
                'formatted' => number_format($coupon->amount, 2)
            ],
            'max_usages' => $coupon->max_usages,
            'description' => $coupon->description,
            'extra_data' => (array) $coupon->extra_data,
            'expires_at' => !empty($coupon->expires_at) ? $coupon->expires_at->toIso8601String() : null,
            'usages' => !empty($coupon->usages_count) ? $coupon->usages_count : $coupon->usages()->count(),
            'is_trashed' => !empty($coupon->deleted_at),
            'trashed_at' => !empty($coupon->deleted_at) ? $coupon->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($coupon->updated_at) ? $coupon->updated_at->toIso8601String() : null,
            'created_at' => $coupon->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param Coupon $coupon
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includePlan(Coupon $coupon)
    {
        if (empty($coupon->plan_id)) {
            return null;
        }
        return $this->item($coupon->plan, new PlanTransformer(), 'plan');
    }
    
    /**
     * @param Coupon        $coupon
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeUsages(Coupon $coupon, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $usages = $coupon->usages()->take($limit)->offset($offset)->oldest()->get();
        return $this->collection($usages, new CouponUsageTransformer(), 'coupon_usage');
    }
    
    /**
     * @param Coupon $coupon
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeUser(Coupon $coupon)
    {
        if (empty($coupon->user_id)) {
            return null;
        }
        return $this->item($coupon->user, new UserTransformer(), 'user');
    }
}