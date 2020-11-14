<?php

namespace App\Notifications\Directory;


use App\Models\Company;
use App\Models\ProfessionalService;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestRejected extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var Company */
    public $company;
    
    /** @var ServiceRequest  */
    public $request;
    
    /** @var ProfessionalService */
    public $service;
    
    /**
     * ServiceRequestRejected constructor.
     *
     * @param ServiceRequest $request
     */
    public function __construct(ServiceRequest $request)
    {
        $this->request = $request;
        $this->service = $request->service;
        $this->company = $request->company;
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
        $subject = 'Service Request for '.$this->service->title . ' was rejected!';
        $message = new MailMessage();
        if (!empty($this->company->email)) {
            $message->cc($this->company->email, $this->company->name);
        }
        return $message->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->greeting('Hi '.$notifiable->firstname)
                        ->line('Your request for the '.$this->service->title.' service was not accepted by the provider.')
                        ->line('You can check the directory for more service providers');
    }
}