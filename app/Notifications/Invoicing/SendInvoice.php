<?php

namespace App\Notifications\Invoicing;


use App\Mail\Invoicing\Invoice;
use App\Models\BankAccount;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendInvoice extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var BankAccount  */
    public $account;

    /** @var Order  */
    public $order;

    /** @var Integration  */
    public $gateway;
    
    /**
     * SendInvoice constructor.
     *
     * @param Order            $order
     * @param Integration|null $gateway
     * @param BankAccount|null $account
     */
    public function __construct(Order $order, Integration $gateway = null, BankAccount $account = null)
    {
        $this->order = $order;
        $this->gateway = $gateway;
        $this->account = $account;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return Invoice
     */
    public function toMail($notifiable)
    {
        $payUrl = null;
        if (!empty($this->gateway)) {
            $query = ['channel' => $this->gateway->name, 'customer' => $notifiable->uuid];
            # query parameters for the payment URL
            $payUrl = web_url('/orders/' . $this->order->uuid . '/pay', $query);
            //$payUrl = web_url(['orders', $this->order->uuid, 'pay'], $query);
        }
        return (new Invoice($notifiable, $this->order, $payUrl, $this->account))->to($notifiable);
    }
}