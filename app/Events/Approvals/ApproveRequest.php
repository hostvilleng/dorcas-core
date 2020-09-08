<?php

namespace App\Events\Approvals;


use App\Events\Event;
use App\Models\ApprovalRequests;
use App\Models\Employee;
use App\Models\User;

class ApproveRequest extends Event
{


  /** @var User  */
  public $user;

  /**
   * @var string
   */
  public $type;


  /**
   * ApprovalRequest constructor.
   *
   * @param User $user
   * @param string $type
   */
  public function __construct(User $user, $type)
      {
        $this->user = $user;
        $this->type = $type;
      }
}
