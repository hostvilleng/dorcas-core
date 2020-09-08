<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\LeaveRequests;
use App\Models\LeaveTypes;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class LeaveTypesTransformer extends TransformerAbstract
{
    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['approvals'];



    /**
     * @param LeaveTypes $leaveTypes
     *
     * @return array
     */
    public function transform(LeaveTypes $leaveTypes)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $leaveTypes->uuid,
            'title' => $leaveTypes->title,
            'updated_at' => $leaveTypes->updated_at->toIso8601String(),
            'created_at' => $leaveTypes->created_at->toIso8601String(),
            'links' => [
                'self' => url('/leave-types', [$leaveTypes->uuid])
            ]
        ];
        return $resource;
    }

    /**
     * @param LeaveTypes $leaveTypes
     *
     * @return \League\Fractal\Resource\Item
     */


    public function includeCompany(LeaveTypes $leaveTypes)
    {
        return $this->item($leaveTypes->company, new CompanyTransformer(), 'company');
    }

    public function includeApprovals(LeaveTypes $leaveTypes){
        return $this->item($leaveTypes->approvals, new ApprovalsTransformer(), 'approvals');
    }

//    public function includeLeaveGroups(LeaveTypes $leaveTypes){
//        return $this->item($leaveTypes->groups,new LeaveGroupsTransformer(),'leaveGroups');
//    }




}