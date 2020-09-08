<?php

namespace App\Notifications;


use App\Models\Invite;
use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Mail\InviteEmail;

class InviteNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var Invite  */
    public $invite;
    
    /** @var mixed  */
    public $inviter;
    
    /** @var string  */
    public $respondLink;
    
    /** @var Partner  */
    public $partner;
    
    /** @var array  */
    public $app;
    
    /** @var string  */
    public $logo;
    
    /**
     * InviteNotification constructor.
     *
     * @param Invite $invite
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
        $this->inviter = $invite->inviter;
        $path = '/invites/' . $this->invite->uuid;
        if ($this->inviter instanceof Partner) {
            $issuance = $this->inviter->domainIssuances()->first();
            # get the first one
            $suffix = app()->environment() === 'production' ? 'dorcas.io' : 'dorcas.local';
            $baseUrl = !empty($issuance) ? 'http://' . $issuance->prefix . '.' . $suffix : null;
            $this->respondLink = !empty($baseUrl) ? custom_url($baseUrl, $path) : site_url($path);
        } else {
            $this->respondLink = site_url($path);
        }
        $this->app = $this->inviter->extra_data['hubConfig'] ?? [];
        $this->partner = $this->inviter;
        $this->logo = $this->inviter->logo_url;
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
        $action = $this->invite->config_data['action'];
        if ($action === 'invite_business') {
            return $this->getCompanyEmail($notifiable);
        } elseif ($action === 'invite_user') {
            return $this->getUserEmail($notifiable);
        }
        return null;
    }
    
    /**
     * @param $notifiable
     *
     * @return MailMessage
     */
    protected function getCompanyEmail($notifiable)
    {
        $productName = 'Dorcas';
        $invitedCompany = $this->invite->config_data['business'];
        $inviteSubject = $this->getSubject();
        $inviteBody = $this->inviter->name . ' just sent your business "' . $invitedCompany . '" an invite to join ' . $productName;
        $inviteFooter = 'If there are any questions, please contact ' . $this->inviter->name .'. If however you got this email in error, please ignore and delete.';
        if ($this->inviter instanceof Partner) {
            $hubConfig = $this->inviter->extra_data['hubConfig'] ?? [];
            # get the hub config
            $productName = $hubConfig['product_name'] ?? 'Dorcas';
            $inviteConfig = $this->inviter->extra_data['inviteConfig'] ?? [];
            # get the invite config
            $inviteSubject = htmlspecialchars_decode($inviteConfig['email_subject']) ?? $inviteSubject;
            $inviteBody = htmlspecialchars_decode($inviteConfig['email_body']) ?? $inviteBody;
            $inviteFooter = htmlspecialchars_decode($inviteConfig['email_footer']) ?? $inviteFooter;
            $this->partner = $this->inviter;
        }

        $viewData = array('app'=>$this->app, 'partner' => $this->partner, 'logo' => $this->logo);

        return (new MailMessage)->from(config('mail.from.address'), $this->inviter->name)
                                ->subject($inviteSubject)
                                ->greeting('Dear ' . $this->invite->firstname)
                                ->line(new HtmlString($inviteBody))
                                ->line(
                                    new HtmlString('<b>Your Login Details</b>:<br/><b>Username</b>: ' . $this->invite->email . '<br/><b>Password</b>: <em>you can set this when you respond</em>')
                                )
                                ->action('Respond to Invitation', $this->respondLink)
                                ->line(
                                    new HtmlString('Click the button above to get started right away!')
                                )
                                ->line(new HtmlString($inviteFooter))
                                ->markdown('notifications::email', $viewData);

        //$inviteConfig = array($inviteSubject, $inviteBody, $inviteFooter);

        //return (new InviteEmail($notifiable, $this->invite, $this->inviter, $this->respondLink, $inviteConfig))->to($notifiable);
    }
    
    /**
     * @param $notifiable
     *
     * @return MailMessage
     */
    protected function getUserEmail($notifiable)
    {
        $productName = 'Dorcas';
        if ($this->inviter instanceof Partner) {
            $hubConfig = $this->inviter->extra_data['hubConfig'] ?? [];
            # get the hub config
            $productName = $hubConfig['product_name'] ?? 'Dorcas';
        }
        return (new MailMessage)->from(config('mail.from.address'), $this->inviter->name)
                                ->subject($this->getSubject())
                                ->greeting('Hi ' . $this->invite->firstname)
                                ->line($this->inviter->name . ' just sent you an invite to join them on ' . $productName)
                                ->action('Respond to Invitation', $this->respondLink)
                                ->line('If there are any questions, please contact ' . $this->inviter->name .'. If however you got this email in error, please ignore and delete.');
    }
    
    /**
     * @return string
     */
    private function getSubject(): string
    {
        $subject = 'An invite was just sent to you from ' . $this->inviter->name;
        $action = $this->invite->config_data['action'];
        switch ($action) {
            case 'invite_business':
                $company = $this->invite->config_data['business'];
                $subject = $this->inviter->name . ' just sent your business ' . $company . ' an invite.';
                break;
            case 'invite_user':
                $subject = $this->inviter->name . ' just sent you an invite to join them.';
                break;
        }
        return $subject;
    }
}