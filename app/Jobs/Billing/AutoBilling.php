<?php

namespace App\Jobs\Billing;


use App\Jobs\Job;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class AutoBilling extends Job
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $builder = Company::where('access_expires_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                            ->whereNotNull('extra_data');
        # select the companies
        foreach ($builder->cursor() as $company) {
            # loop through the matching companies
            $configuration = $company->extra_data;
            if (isset($configuration['billing']['auto_billing']) && $configuration['billing']['auto_billing'] === false) {
                # auto billing is disabled for the company
                continue;
            }
            if (empty($configuration['paystack_authorization_code'])) {
                # this person has never paid a bill via paystack yet
                continue;
            }
            Queue::push(new ChargeForPlan($company));
        }
        return;
    }
}