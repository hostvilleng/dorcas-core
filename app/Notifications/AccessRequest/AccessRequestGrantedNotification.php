<?php

namespace App\Notifications\AccessRequest;


use App\Models\UserAccessGrant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessRequestGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var UserAccessGrant  */
    public $accessGrant;
    
    /** @var string  */
    public $respondLink;
    
    public function __construct(UserAccessGrant $accessGrant)
    {
        $this->accessGrant = $accessGrant;
        $this->respondLink = site_url('directory/access-grants');
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
            $this->respondLink = url_from_domain_issuance($issuance, 'directory/access-grants');
            $hubConfig = $partner->extra_data['hubConfig'] ?? [];
            # get the hub config
        }
        $productName = $hubConfig['product_name'] ?? 'Hub';
        $company = $this->accessGrant->company;
        $companyUser = $company->users()->oldest()->first();
        return (new MailMessage)->from(config('mail.from.address'), $company->name)
                                ->replyTo($companyUser->email, $companyUser->name)
                                ->subject($productName . ' module access approved')
                                ->greeting('Hi ' . $notifiable->firstname)
                                ->line(
                                    $company->name . ' has just approved your access to some of their modules.'
                                )
                                ->action('View Request', $this->respondLink)
                                ->line(
                                    'If there are any questions, please contact ' . $company->name .
                                    ' by replying to this email for extra information. If however you got this email '.
                                    'in error, please ignore and delete.'
                                );
    }
}