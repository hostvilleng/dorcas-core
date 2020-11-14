<?php

namespace App\Notifications\Billing;


use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutoBillingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    /** @var Company  */
    public $company;
    
    /** @var Carbon  */
    public $date;
    
    /**
     * AutoBillingReminderNotification constructor.
     *
     * @param Company $company
     * @param Carbon  $date
     */
    public function __construct(Company $company, Carbon $date)
    {
        $this->company = $company;
        $this->date = $date;
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
        $partner = $notifiable->partner()->with(['domainIssuances', 'domainIssuances.domain'])->first();
        $partnerConfiguration = !empty($partner) ? $partner->extra_data : [];
        # get the configuration
        $hubConfig = $partnerConfiguration['hubConfig'] ?? [];
        $settingsUrl = site_url('/settings/billing');
        if (!empty($partner)) {
            $hubConfig['product_logo'] = !empty($partner->logo) ? $partner->logo : null;
        }
        if (!empty($partner->domainIssuances) && $partner->domainIssuances->count() > 0) {
            $settingsUrl = url_from_domain_issuance($partner->domainIssuances->first(), '/settings/billing');
        }
        $daysDiff = Carbon::now()->diffInDays($this->date);
        $productName = $hubConfig['product_name'] ?? config('app.name');
        return (new MailMessage)->from(config('mail.from.address'), $productName . ' Billing Reminder')
                                ->subject('Your next billing date is in ' . $daysDiff . ' days')
                                ->greeting('Hi ' . $notifiable->firstname)
                                ->line('This email is a reminder to let you now your account will be automatically billed on ' . $this->date->format('D jS M, Y') . '.')
                                ->line('This only applies if auto-billing is enabled on your account; if you have turned it off, feel free to ignore this email.')
                                ->action('Check Billing Settings', $settingsUrl)
                                ->line('If there are any questions, please contact us by replying this email. If however you got this email in error, please ignore and delete.');
    }
}