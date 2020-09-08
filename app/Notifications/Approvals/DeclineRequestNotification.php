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

class DeclineRequestNotification extends Notification implements ShouldQueue
{
  use Queueable;
  /**
   * @var string
   */
  private $type;

  /**
   * SendInvoice constructor.
   *
   * @param ApprovalRequests|null $request
   */
  public function __construct(string $type)
  {
    $this->type = $type;


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
        $hubConfig = $partner->extra_data['hubConfig'] ?? [];
        # get the hub config
      }
      $subject = '';
      $reason = '';
      switch ($this->type){
        case 'leave':
          $subject = 'Leave Request  Declined  ';
          $reason = 'Your Leave Request Has Been Declined.. Kindly Logon to Dashboard to update or view declination comments';
          break;
        case  'payroll':
          $subject = 'Payroll Request Declined';
          break;
        default:
          break;
      }

      $message = new MailMessage();
      return $message
        ->subject($subject)
        ->from(config('mail.from.address'))
        ->replyTo($notifiable->email, $notifiable->firstname)
        ->greeting('Good day ' . $notifiable->firstname)
        ->line($reason)
        ->markdown('vendor.notifications.email', ['partner' => $partner]);
    } catch (Exception $ex) {
      Log::error($ex->getMessage());
    }
  }

}

