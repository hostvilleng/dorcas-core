<?php

namespace App\Events\Invites;


use App\Models\Invite;

class InviteCreated
{
    /** @var Invite  */
    public $invite;
    
    /**
     * InviteCreated constructor.
     *
     * @param Invite $invite
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }
}