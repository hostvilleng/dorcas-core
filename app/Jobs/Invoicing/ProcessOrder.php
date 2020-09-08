<?php

namespace App\Jobs\Invoicing;


use App\Jobs\Job;
use App\Models\Order;
use App\Notifications\Invoicing\SendInvoice;
use Illuminate\Support\Facades\Notification;

class ProcessOrder extends Job
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * Order of priority for which payment integrations are checked.
     *
     * @var array
     */
    protected $priority = [
        'rave',
        'paystack'
    ];

    /**
     * ProcessOrder constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $paymentGateway = null;
        # a payment integration
        $account = null;
        # the owner bank account
        if (!self::isServiceRequestTitle($this->order->title)) {
            $integrations = $this->order->company->integrations()->where('type', 'payment')->get();
            # get the payment integrations for the company that owns the order
            if (!empty($integrations) && $integrations->count() > 0) {
                # there are payment-based integrations
                foreach ($this->priority as $name) {
                    $integration = $integrations->where('name', $name)->first();
                    if ($integration === null) {
                        continue;
                    }
                    $paymentGateway = $integration;
                    break;
                }
            }
        } else {
            # get the bank account for the service provider
            $companyOwner = $this->order->company->users()->first();
            $account = $companyOwner->bankAccounts->first();
            # get the bank account belonging to the user
        }
        $customers = $this->order->customers;
        # get the customers on this order; we need to notify them
        Notification::send($customers, new SendInvoice($this->order, $paymentGateway, $account));
        # send the notification
    }
    
    /**
     * Checks if this string matches the format of a service request.
     *
     * @param string $title
     *
     * @return bool
     */
    public static function isServiceRequestTitle(string $title): bool
    {
        $serviceTitles = ['service request for '];
        $title = strtolower($title);
        # convert the title
        return starts_with($title, $serviceTitles);
    }
}