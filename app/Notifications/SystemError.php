<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SystemError extends Notification
{

    /** @var \Throwable  */
    public $exception;

    /** @var array  */
    public $extras;

    public function __construct(\Throwable $e, array $extraData = [])
    {
        $this->exception = $e;
        $this->extras = $extraData;
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
        $subject = 'System error from Dorcas';
        return (new MailMessage())->subject($subject)
                                ->from(config('mail.from.address'), config('mail.from.name'))
                                ->error()
                                ->line($this->exception->getMessage())
                                ->line('See attached JSON data:')
                                ->line(new HtmlString('<pre><code>'. json_encode($this->extras) . '</code></pre>'));
    }
}