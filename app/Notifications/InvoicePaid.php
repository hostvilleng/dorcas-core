<?php

namespace App\Notifications;


use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var Customer  */
    public $customer;

    /** @var Order  */
    public $order;

    /** @var PaymentTransaction  */
    public $transaction;
    
    /** @var array */
    public $app;
    
    /** @var Partner  */
    public $partner;
    
    /** @var string  */
    public $logo;
    
    /** @var string  */
    public $paid_notification_email;

    /**
     * InvoicePaid constructor.
     *
     * @param Order                   $order
     * @param Customer                $customer
     * @param PaymentTransaction      $transaction
     */
    public function __construct(Order $order, Customer $customer, PaymentTransaction $transaction)
    {
        $this->order = $order;
        $this->customer = $customer;
        $this->transaction = $transaction;
        $this->app = self::getPartnerAppSettings($order->company->users()->first());
        $this->partner = self::getPartner($order->company->users()->first());
        $this->logo = !empty($order->company->logo) ? $order->company->logo : \App\Http\Controllers\Invoicing\Invoice::DEFAULT_IMAGE;
        //$logo = !empty($this->company->logo) ? $this->company->logo : \App\Http\Controllers\Invoicing\Invoice::DEFAULT_IMAGE;
        if (!empty($order->company->extra_data))  {
            $extra_data = $order->company->extra_data;
            $settings = $extra_data['store_settings'] ?? [];
            if (!empty($settings["store_paid_notifications_email"])) {
                $this->paid_notification_email = $settings["store_paid_notifications_email"];
            }
        }
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
     *
     * @param User|Partner|Company|null $user
     *
     * @return array
     */
    public static function getPartner($user = null): array
    {
        $partner = null;
        if ($user instanceof Partner) {
            $partner = $user;
        } elseif ($user instanceof Company) {
            $user = $user->users()->first();
            $partner = $user->partner;
        } elseif ($user instanceof User) {
            if (empty($user)) {
                return [];
            }
            $partner = !empty($user) ? $user->partner : null;
        } else {
            return [];
        }
        if (!empty($partner)) {
            return [];
        }
        return !empty($partner) ? $partner : [];
    }

    /**
     *
     * @param User|Partner|Company|null $user
     *
     * @return array
     */
    public static function getPartnerAppSettings($user = null): array
    {
        $partner = null;
        if ($user instanceof Partner) {
            $partner = $user;
        } elseif ($user instanceof Company) {
            $user = $user->users()->first();
            $partner = $user->partner;
        } elseif ($user instanceof User) {
            if (empty($user)) {
                return [];
            }
            $partner = !empty($user) ? $user->partner : null;
        } else {
            return [];
        }
        if (!empty($partner)) {
            return [];
        }
        return !empty($partner->extra_data) && !empty($partner->extra_data['hubConfig']) ?
            $partner->extra_data['hubConfig'] : [];
    }

    /**
     * @param $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = 'Payment made on Invoice #'.$this->order->invoice_number;
        if ($this->order->items->count() > 0) {
            $items = $this->order->items->map(function ($orderItem) {
                return [
                    'quantity' => $orderItem->pivot->quantity,
                    'product' => $orderItem->name,
                    'unit_price' => $orderItem->pivot->unit_price,
                ];
            })->all();
        } else {
            $items = [[
                'quantity' => $this->order->quantity,
                'product' => $this->order->product_name,
                'unit_price' => $this->order->unit_price
            ]];
        }


        $viewData = array('app'=>$this->app, 'partner' => $this->partner, 'logo' => $this->logo);

        $mail = (new MailMessage())->subject($subject)
                                    ->markdown('notifications::email', $viewData)
                                    ->from(config('mail.from.address'), config('mail.from.name'))
                                    ->greeting('Hi '.$notifiable->firstname)
                                    ->line($this->customer->name . ' has just made a payment on Invoice #'.$this->order->invoice_number)
                                    ->line('See the details of the transaction below:')
                                    ->line(new HtmlString('<strong>Channel: </strong>' . title_case($this->transaction->channel)))
                                    ->line(new HtmlString('<strong>Reference: </strong>' . $this->transaction->reference))
                                    ->line(new HtmlString('<strong>Amount: </strong>' . $this->transaction->currency . ' ' . number_format($this->transaction->amount, 2)))
                                    ->line(new HtmlString('<strong>Successful: </strong>' . ($this->transaction->is_successful ? 'Yes' : 'No')))
                                    ->line(new HtmlString('<hr><br>'))
                                    ->line(new HtmlString('<strong>Customer Details(s)</strong>'))
                                    ->line(new HtmlString('<strong>Name: </strong>' . $this->customer->name ))
                                    ->line(new HtmlString('<strong>Email: </strong>' . $this->customer->email))
                                    ->line(new HtmlString('<strong>Phone: </strong>' . $this->customer->phone))
                                    ->line(new HtmlString('<strong>Address: </strong>' . $this->customer->address))
                                    ->line(new HtmlString('<hr><br>'))
                                    ->line(new HtmlString('<strong>Product(s)</strong>'));
        foreach ($items as $item) {
            $mail = $mail->line(new HtmlString('<strong>Product: </strong>'.$item['quantity'].' unit(s) of '.$item['product'].' @ '.$this->order->currency.''.$item['unit_price']));
        }
        if (!empty($this->paid_notification_email))  {
            $mail = $mail->cc($this->paid_notification_email);
        }
        return $mail;
    }
}