<?php

namespace App\Listeners\Approvals;


use App\Events\Approvals\DeclineRequest as Event;
use App\Notifications\Approvals\DeclineRequestNotification as DeclineNoty;
use App\Notifications\Approvals\sendRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeclineRequest implements ShouldQueue
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
    $user = $event->user;
    # get the first user in the requesting company
    $user->notify(new DeclineNoty($event->type));
    return;
  }
}