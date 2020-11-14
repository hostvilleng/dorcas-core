<?php

namespace App\Notifications\Approvals;
use App\Models\ApprovalRequests;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery\Exception;
use Illuminate\support\Facades\Log;
use Illuminate\Support\HtmlString;

class sendRequest extends Notification implements ShouldQueue
{
    use Queueable;
    /** @var $approvalRequest */
    public $approvalRequest;

    /** @var $respondLink */
    public $respondLink;

    /**
     * SendInvoice constructor.
     *
     * @param ApprovalRequests|null $request
     */
    public function __construct(ApprovalRequests $request)
    {
        $this->approvalRequest = $request;

        $this->respondLink = site_url('mpe/approval/request/' . $this->approvalRequest->uuid);

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
     * @param mixed $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
      return $this->sendMail($notifiable);

    }

    private function sendMail($notifiable){
      try {
        $partner = $notifiable->partner;
        $hubConfig = [];
        if (!empty($partner)) {
          $issuance = $partner->domainIssuances()->first();
          $this->respondLink = url_from_domain_issuance($issuance, 'mpe/approval/request/' . $this->approvalRequest->uuid);
          $hubConfig = $partner->extra_data['hubConfig'] ?? [];
          # get the hub config
        }
        $subject = 'Service Request for ' . $this->approvalRequest->approvals->title . ' from ' . $notifiable->company->name;
        $message = new MailMessage();
        return $message
          ->subject($subject)
          ->from(config('mail.from.address'), $this->approvalRequest->company->name)
          ->replyTo($notifiable->email, $notifiable->firstname)
          ->greeting('Good day ' . $notifiable->firstname)
          ->line(' A request has been sent and needs your approval')
          ->action('Respond to Request', $this->respondLink)
          ->markdown('vendor.notifications.email', ['partner' => $partner]);
      } catch (Exception $ex) {
        Log::error($ex->getMessage());
      }
    }

}

