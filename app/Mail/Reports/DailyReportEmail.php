<?php

namespace App\Mail\Reports;


use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    /** @var array */
    public $stats;
    
    public function __construct(array $data)
    {
        $this->stats = $data;
    }
    
    public function build()
    {
        $subject = 'Platform Report for '.Carbon::yesterday()->format('D jS M, Y');
        return $this->from('no-reply@dorcas.io', 'Dorcas Report Bot')
                    ->subject($subject)
                    ->markdown('emails.reports.daily')
                    ->with(['subject' => $subject]);
    }
}