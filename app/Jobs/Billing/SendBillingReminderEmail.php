<?php

namespace App\Jobs\Billing;


use App\Jobs\Job;
use App\Models\Company;
use App\Notifications\Billing\AutoBillingReminderNotification;
use Carbon\Carbon;

class SendBillingReminderEmail extends Job
{
    /** @var Company  */
    public $company;
    
    /** @var Carbon  */
    public $date;
    
    /**
     * SendBillingReminderEmail constructor.
     *
     * @param Company $company
     * @param Carbon  $date
     */
    public function __construct(Company $company, Carbon $date)
    {
        $this->company = $company;
        $this->date = $date;
    }
    
    public function handle()
    {
        $configuration = $this->company->extra_data;
        if (isset($configuration['billing']['auto_billing']) && $configuration['billing']['auto_billing'] === false) {
            # auto billing is disabled for the company
            return;
        }
        if (empty($configuration['paystack_authorization_code'])) {
            # this person has never paid a bill via paystack yet
            return;
        }
        $user = $this->company->users()->oldest()->first();
        $user->notify(new AutoBillingReminderNotification($this->company, $this->date));
    }
}