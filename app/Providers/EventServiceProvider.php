<?php

namespace App\Providers;


use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\AccountRegistered::class => [
            \App\Listeners\AccountRegisteredListener::class
        ],
        \App\Events\Professional\NewServiceRequest::class => [
            \App\Listeners\Professional\NewServiceRequest::class
        ],
        \App\Events\Professional\ServiceRequestStatusChanged::class => [
            \App\Listeners\Professional\ServiceRequestStatusChanged::class
        ],
        \App\Events\Invites\InviteCreated::class => [
            \App\Listeners\Invites\InviteCreatedListener::class
        ],
        \App\Events\ModuleAccess\AccessRequestedEvent::class => [
            \App\Listeners\ModuleAccess\AccessRequestedListener::class
        ],
        \App\Events\ModuleAccess\AccessGrantedEvent::class => [
            \App\Listeners\ModuleAccess\AccessGrantedListener::class
        ],
        \App\Events\Approvals\NewApprovalRequest::class => [
            \App\Listeners\Approvals\NewAprovalRequest::class
        ],
        \App\Events\Approvals\ApproveRequest::class => [
          \App\Listeners\Approvals\ApproveRequest::class
        ],
        \App\Events\Approvals\DeclineRequest::class => [
          \App\Listeners\Approvals\DeclineRequest::class
        ],
        /*,'Illuminate\Notifications\Events\NotificationSent' => [
            \App\Listeners\LogNotifications::class
        ]*/
    ];
    
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
