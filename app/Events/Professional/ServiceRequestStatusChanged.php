<?php

namespace App\Events\Professional;


use App\Events\Event;
use App\Models\ServiceRequest;

class ServiceRequestStatusChanged extends Event
{
    /** @var ServiceRequest  */
    public $request;
    
    /**
     * ServiceRequestAccepted constructor.
     *
     * @param ServiceRequest $request
     */
    public function __construct(ServiceRequest $request)
    {
        $this->request = $request;
    }
}