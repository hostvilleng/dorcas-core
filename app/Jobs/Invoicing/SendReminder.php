<?php

namespace App\Jobs\Invoicing;


use App\Jobs\Job;
use App\Models\Order;
use App\Notifications\Invoicing\SendInvoiceReminder;
use Illuminate\Support\Facades\Notification;

class SendReminder extends Job
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
        $customers = $this->order->customers()->where('is_paid', 0)->get();
        # get the customers on this order that have not paid; we need to notify them
        if ($customers->count() === 0) {
            return;
        }
        $paymentGateway = null;
        # a payment integration
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
        Notification::send($customers, new SendInvoiceReminder($this->order, $paymentGateway));
        # send the notification
    }
}