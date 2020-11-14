<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApprovalRequests;
use App\Models\Approvals;
use App\Models\LeaveRequests;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class LeaveRequestsTransformer extends TransformerAbstract
{
    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['leavegroup','employees','leavetype'];



    /**
     * @param LeaveRequests $requests
     *
     * @return array
     */
    public function transform(LeaveRequests $leaveRequests)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $leaveRequests->uuid,
            'days_available' => $leaveRequests->count_available,
            'days_utilized' => $leaveRequests->count_utilized,
            'days_remaining' => $leaveRequests->count_remaining,
            'days_requesting' => $leaveRequests->count_requesting,
            'start_date' => $leaveRequests->data_start_date,
            'report_back' => $leaveRequests->data_report_back,
            'status' => $leaveRequests->status,
            'contact_address' => $leaveRequests->data_contact_address,
            'contact_phone' => $leaveRequests->data_contact_phone,
            'backup_staff' => $leaveRequests->data_backup_staff,
            'remarks' => $leaveRequests->data_remarks,
            'rejection_comments' => json_decode($leaveRequests->rejection_comments,true),
            'updated_at' => $leaveRequests->updated_at->toIso8601String(),
            'created_at' => $leaveRequests->created_at->toIso8601String(),
            'links' => [
                'self' => url('/approval-request', [$leaveRequests->uuid])
            ]
        ];
        return $resource;
    }

    /**
     * @param LeaveRequests $leaveRequests
     *
     * @return \League\Fractal\Resource\Item
     */




    public function  includeLeaveGroup(LeaveRequests $leaveRequests, ParamBag $params = null){

        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $groups = $leaveRequests->groups()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($groups, new LeaveGroupsTransformer(), 'leaveGroups');
    }

    public function  includeLeaveType(LeaveRequests $leaveRequests, ParamBag $params = null){

        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $groups = $leaveRequests->leaveTypes()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($groups, new LeaveTypesTransformer(), 'leaveType');
    }

    public function includeEmployees(LeaveRequests $leaveRequests, ParamBag $params = null){
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees  = $leaveRequests->employees()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employees');
    }



}
