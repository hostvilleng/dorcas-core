<?php

namespace App\Notifications\Directory;


use App\Models\Company;
use App\Models\ProfessionalService;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewServiceRequest extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var Company */
    public $company;
    
    /** @var ServiceRequest  */
    public $request;
    
    /** @var ProfessionalService */
    public $service;
    
    /**
     * NewServiceRequest constructor.
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
        $subject = 'Service Request for '.$this->service->title . ' from '.$this->company->name;
        $companyUser = $this->company->users()->firstOrFail();
        # get the first user
        $message = new MailMessage();
        if (!empty($this->company->email)) {
            $message->cc($this->company->email, $this->company->name);
        }
        return $message->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->replyTo($companyUser->email, $companyUser->firstname .' '.$companyUser->lastname)
                        ->greeting('Hi '.$notifiable->firstname)
                        ->line('A business on Dorcas has just sent a request for your '.$this->service->title.' service.')
                        ->line(new HtmlString('<strong>Additional Message: </strong><br>' . strval($this->request->message)))
                        ->line('Sign in to your account, and view the request.');
    }
}