<?php

namespace App\Jobs\Billing;


use App\Jobs\Job;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class AutoBillingReminder extends Job
{
    
    public function handle()
    {
        $inXDays = Carbon::now()->addDays(3);
        $dateString = $inXDays->format('Y-m-d');
        $builder = Company::whereBetween('access_expires_at', [$dateString . ' 00:00:00', $dateString . ' 23:59:59'])
                            ->whereIn('plan_id', [2, 3])
                            ->whereNotNull('extra_data');
        # select the companies
        foreach ($builder->cursor() as $company) {
            # loop through the matching companies
            Queue::push(new SendBillingReminderEmail($company, $inXDays));
        }
        return;
    }
}