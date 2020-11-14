<?php

namespace App\Notifications\Invoicing;


use App\Mail\Invoicing\PaymentReminder;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendInvoiceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var Order  */
    public $order;

    /** @var Integration  */
    public $gateway;
    
    /**
     * SendInvoiceReminder constructor.
     *
     * @param Order            $order
     * @param Integration|null $gateway
     */
    public function __construct(Order $order, Integration $gateway = null)
    {
        $this->order = $order;
        $this->gateway = $gateway;
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
     * @return PaymentReminder
     */
    public function toMail($notifiable)
    {
        $payUrl = null;
        if (!empty($this->gateway)) {
            $query = ['channel' => $this->gateway->name, 'customer' => $notifiable->uuid];
            # query parameters for the payment URL
            $payUrl = web_url(['orders/' . $this->order->uuid . '/pay'],$query);
            //$payUrl = web_url(['orders', $this->order->uuid, 'pay'], $query);
        }
        return (new PaymentReminder($notifiable, $this->order, $payUrl))->to($notifiable);
    }
}