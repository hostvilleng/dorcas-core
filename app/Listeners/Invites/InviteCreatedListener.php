<?php

namespace App\Listeners\Invites;


use App\Events\Invites\InviteCreated;
use App\Notifications\InviteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InviteCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * @param InviteCreated $event
     */
    public function handle(InviteCreated $event)
    {
        $event->invite->notify(new InviteNotification($event->invite));
    }
}