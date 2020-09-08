<?php

namespace App\Listeners\ModuleAccess;


use App\Events\ModuleAccess\AccessRequestedEvent;
use App\Notifications\AccessRequest\ModuleAccessRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AccessRequestedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    public function handle(AccessRequestedEvent $event)
    {
        $company = $event->accessGrant->company;
        $principal = $company->users()->oldest()->first();
        # get the principal account owner
        $principal->notify(new ModuleAccessRequestNotification($event->accessGrant));
    }
}