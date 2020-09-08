<?php

namespace App\Listeners;


use Illuminate\Notifications\Events\NotificationSent
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotificationsListener implements ShouldQueue
{
    use InteractsWithQueue;


	/**
	 * Handle the event.
	 *
	 * @param  NotificationSent  $event
	 * @return void
	 */
	public function handle(NotificationSent $event)
	{
	    // $event->channel
	    // $event->notifiable
	    // $event->notification
	    // $event->response
	}

}