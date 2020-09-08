<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var Invite  */
    public $invite;

    /** @var Invite  */
    public $company;
    
    /** @var mixed  */
    public $inviter;
    
    /** @var string  */
    public $respondLink;
    
    /** @var array  */
    public $app;

    /** @var array  */
    public $inviteConfig;
    
    /**
     * Invitation constructor.
     *
     * @param                  $notifiable
     * @param Invite            $invite
     * @param Partner            $inviter
     * @param string      $respondLink
     * @param array $inviteConfig
     */
    public function __construct($notifiable, Invite $invite, Partner $inviter, string $respondLink, array $inviteConfig)
    {
        $this->invite = $invite;
        $this->inviter = $inviter;
        $this->respondLink = $respondLink;
        $this->inviteConfig = $inviteConfig;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $productName = 'Dorcas';
        $invitedCompany = $this->invite->config_data['business'];
        $inviteSubject = $this->getSubject();
        $inviteBody = $this->inviter->name . ' just sent your business "' . $invitedCompany . '" an invite to join ' . $productName;
        $inviteFooter = 'If there are any questions, please contact ' . $this->inviter->name .'. If however you got this email in error, please ignore and delete.';
        $inviteConfig = $this->inviteConfig;
        if ($this->inviter instanceof Partner) {
            $hubConfig = $this->inviter->extra_data['hubConfig'] ?? [];
            # get the hub config
            $productName = $hubConfig['product_name'] ?? 'Dorcas';
            $inviteConfig = $this->inviter->extra_data['inviteConfig'] ?? [];
            # get the invite config
            $inviteSubject = htmlspecialchars_decode($inviteConfig['email_subject']) ?? $inviteSubject;
            $inviteBody = htmlspecialchars_decode($inviteConfig['email_body']) ?? $inviteBody;
            $inviteFooter = htmlspecialchars_decode($inviteConfig['email_footer']) ?? $inviteFooter;
        }

        $logo = !empty($this->inviter->logo_url) ? $this->inviter->logo_url : \App\Http\Controllers\Invoicing\Invoice::DEFAULT_IMAGE;
        $support_email = $this->inviter->extra_data['support_email'];
        $user = \App\Models\User::find($this->invite->config_data['inviting_user_id']);

        //$company->email, $partner->name
        return $this->subject($inviteSubject)
                    ->from('hello@dorcas.io', $this->inviter->name)
                    ->replyTo($support_email, $this->inviter->name)
                    ->view('emails.invite')
                    ->with(['inviteConfig' => $inviteConfig, 'logo' => $logo, 'link' => $this->respondLink, 'partner' => $this->inviter, 'inviteSubject' => $inviteSubject, 'inviteBody' => $inviteBody, 'inviteFooter' => $inviteFooter]);

        /*return $this->from(config('mail.from.address'), $this->inviter->name)
                                ->subject($inviteSubject)
                                ->greeting('Dear ' . $this->invite->firstname)
                                ->line($inviteBody)
                                ->action('Respond to Invitation', $this->respondLink)
                                ->line($inviteFooter);*/



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