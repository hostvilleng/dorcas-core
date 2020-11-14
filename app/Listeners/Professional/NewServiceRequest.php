<?php

namespace App\Listeners\Professional;


use App\Events\Professional\NewServiceRequest as Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewServiceRequest implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle(Event $event)
    {
        if (!$event->request->is_pending) {
            # it's not in the pending state
            throw new \UnexpectedValueException('The service requests has already been attended to.');
        }
        $user = $event->request->service->user;
        # get the first user in the requesting company
        $user->notify(new \App\Notifications\Directory\NewServiceRequest($event->request));
        return;
    }
}