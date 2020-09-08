<?php

namespace App\Events\Approvals;


use App\Events\Event;
use App\Models\ApprovalRequests;
use App\Models\User;

class NewApprovalRequest extends Event
{
    /** @var ApprovalRequest  */
    public $request;

    /** @var User  */
    public $user;

    /**
     * ApprovalRequest constructor.
     *
     * @param User $user
     * @param ApprovalRequests $request
     */
    public function __construct(User $user,ApprovalRequests $request )
    {
      $this->request = $request;
      $this->user = $user;
    }
}
