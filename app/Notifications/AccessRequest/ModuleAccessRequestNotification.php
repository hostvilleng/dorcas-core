<?php

namespace App\Notifications\AccessRequest;


use App\Models\UserAccessGrant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleAccessRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var UserAccessGrant  */
    public $accessGrant;

    /** @var string  */
    public $respondLink;

    public function __construct(UserAccessGrant $accessGrant)
    {
        $this->accessGrant = $accessGrant;
        $this->respondLink = site_url('access-grants/' . $accessGrant->uuid);
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
     * @param $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $partner = $notifiable->partner;
        $hubConfig = [];
        if (!empty($partner)) {
            $issuance = $partner->domainIssuances()->first();
            $this->respondLink = url_from_domain_issuance($issuance, 'access-grants/' . $this->accessGrant->uuid);
            $hubConfig = $partner->extra_data['hubConfig'] ?? [];
            # get the hub config
        }
        $productName = $hubConfig['product_name'] ?? 'Hub';
        $user = $this->accessGrant->user;
        return (new MailMessage)->from(config('mail.from.address'), $user->name)
                                ->replyTo($user->email, $user->name)
                                ->subject($productName . ' module access request')
                                ->greeting('Hi ' . $notifiable->firstname)
                                ->line(
                                    $user->name . ' just sent your business "' . $this->accessGrant->company->name .
                                    '" a request to gain access to  some modules in your ' . $productName . ' account.'
                                )
                                ->action('Respond to Request', $this->respondLink)
                                ->line(
                                    'If there are any questions, please contact ' . $user->name .
                                    ' by replying to this email for extra information. If however you got this email '.
                                    'in error, please ignore and delete.'
                                )
                                ->view('vendor.notifications.email', ['partner' => $partner]);
    }
}
