<?php

namespace App\Events\ModuleAccess;


use App\Events\Event;
use App\Models\UserAccessGrant;

class AccessGrantedEvent extends Event
{
    /** @var UserAccessGrant  */
    public $accessGrant;
    
    /**
     * AccessRequestedEvent constructor.
     *
     * @param UserAccessGrant $accessGrant
     */
    public function __construct(UserAccessGrant $accessGrant)
    {
        $this->accessGrant = $accessGrant;
    }
}