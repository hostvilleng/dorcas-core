<?php

namespace App\Listeners\ModuleAccess;


use App\Events\ModuleAccess\AccessGrantedEvent;
use App\Notifications\AccessRequest\AccessRequestGrantedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccessGrantedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    public function handle(AccessGrantedEvent $event)
    {
        $event->accessGrant->user->notify(new AccessRequestGrantedNotification($event->accessGrant));
    }
}