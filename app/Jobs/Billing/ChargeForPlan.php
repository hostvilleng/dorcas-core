<?php

namespace App\Jobs\Billing;


use App\Jobs\Job;
use App\Models\Company;
use App\Notifications\SystemError;
use Carbon\Carbon;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ChargeForPlan extends Job
{
    /** @var Company  */
    public $company;

    /**
     * ChargeForPlan constructor.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $plan = $this->company->plan;
        # get the plan
        if ($this->company->plan_type === 'yearly') {
            $amount = $plan->price_yearly;
        } else {
            $amount = $plan->price_monthly;
        }
        $transactionData = [];
        # the transaction data
        try {
            $authCode = $this->company->extra_data['paystack_authorization_code'] ?? null;
            # get the authorization code
            if (empty($authCode)) {
                throw new \UnexpectedValueException(
                    'The authorization code has not been set for the company yet; they will need to be manually charged first.'
                );
            }
            $paystack = new \Yabacon\Paystack(config('services.paystack.secret_key'));
            $charge = $paystack->transaction->charge([
                'authorization_code' => $authCode,
                'amount' => $amount * 100,
                'email' => $this->company->users()->first()->email
            ]);
            # we send the charge request
            if ($charge->data->status !== 'success') {
                # charge failed
                throw new \RuntimeException('Charge action failed for customer using code: '.$authCode);
            }
            $transactionData = [
                'reference' => $charge->data->reference,
                'processor' => 'paystack',
                'plan_id' => $plan->id,
                'currency' => $charge->data->currency,
                'amount' => $charge->data->amount / 100,
                'json_data' => [
                    'gateway_response' => $charge->data->gateway_response,
                    'channel' => $charge->data->channel,
                    'source_ip' => 'auto_biller',
                    'custom_data' => [],
                    'card' => [
                        'auth_code' => $charge->data->authorization->authorization_code,
                        'last4' => $charge->data->authorization->last4,
                        'exp_month' => $charge->data->authorization->exp_month,
                        'exp_year' => $charge->data->authorization->exp_year,
                        'card_type' => $charge->data->authorization->card_type,
                    ]
                ],
                'is_successful' => $charge->data->status === 'success' ? 1 : 0
            ];
            $transaction = $this->company->billPayments()->create($transactionData);
            # add the new bill payment
            $currentExpiry = empty($this->company->access_expires_at) || Carbon::now()->greaterThan($this->company->access_expires_at) ?
                Carbon::now() : $this->company->access_expires_at;
            # set the current expiry
            if ($transaction->amount >= $plan->price_yearly) {
                # person paid for the yearly plan
                $this->company->access_expires_at = $currentExpiry->addYear()->endOfDay();
            } elseif ($transaction->amount >= $plan->price_monthly) {
                $this->company->access_expires_at = $currentExpiry->addMonth()->endOfDay();
            }
            # set the properties
            if (!$this->company->save()) {
                throw new \Exception(
                    'Failed to save, and extend payment plan for business after successful charge.'
                );
            }

        } catch (\UnexpectedValueException $e) {
            Log::info($e->getMessage(), $this->company->toArray());
        } catch (\RuntimeException $e) {
            Log::warning($e->getMessage(), $this->company->toArray());
        } catch (\Exception $e) {
            $this->sendErrorReport($e, $transactionData);
        } catch (\Throwable $e) {
            $this->sendErrorReport($e, $transactionData);
        }
        return;
    }
    
    /**
     * @param \Throwable $e
     * @param array      $transactionData
     */
    private function sendErrorReport(\Throwable $e, array $transactionData = [])
    {
        $users = collect([]);
        foreach (config('dorcas-api.developers') as $email) {
            $users->push(new GenericUser(['email' => $email]));
        }
        $custom = ['company' => $this->company->toArray(), 'txn' => $transactionData];
        Log::error($e->getMessage(), $custom);
    
        Notification::send($users, new SystemError($e, $custom));
    }
}