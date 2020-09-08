<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\LeaveGroups;
use App\Models\LeaveRequests;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class LeaveGroupsTransformer extends TransformerAbstract
{
    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['leavetypes','teams'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['leavetypes','teams'];



    /**
     * @param LeaveGroups $leaveGroups
     *
     * @return array
     */
    public function transform(LeaveGroups $leaveGroups)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $leaveGroups->uuid,
            'group_type' => $leaveGroups->group_type,
            'duration_days' => $leaveGroups->duration_days,
            'duration_term' => $leaveGroups->duration_term,
            'updated_at' => $leaveGroups->updated_at->toIso8601String(),
            'created_at' => $leaveGroups->created_at->toIso8601String(),
            'links' => [
                'self' => url('/leave-groups', [$leaveGroups->uuid])
            ]
        ];
        return $resource;
    }

    /**
     * @param LeaveGroups $leaveGroups
     *
     * @return \League\Fractal\Resource\Collection
     */


    public function includeLeaveTypes(LeaveGroups $leaveGroups,  ParamBag $params = null)
    {
//        return $this->item($leaveGroups->types, new LeaveTypesTransformer(), 'leavetypes');
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $types = $leaveGroups->types()->take($limit)
            ->offset($offset)
            ->oldest('title')
            ->get();
        return $this->collection($types, new LeaveTypesTransformer(), 'leaveTypes');

    }

    public function includeTeams(LeaveGroups $leaveGroups,  ParamBag $params = null)
    {
//        return $this->item($leaveGroups->types, new LeaveTypesTransformer(), 'leavetypes');
        if($leaveGroups->group_type === 'team'){
            list($limit, $offset) = parse_fractal_params($params, 0, 10);
            $teams = $leaveGroups->teams()->take($limit)
                ->offset($offset)
                ->oldest('name')
                ->get();
            return $this->collection($teams, new TeamTransformer(), 'teams');
        }
        else{
            list($limit, $offset) = parse_fractal_params($params, 0, 10);
            $teams = $leaveGroups->departments()->take($limit)
                ->offset($offset)
                ->oldest('name')
                ->get();
            return $this->collection($teams, new DepartmentTransformer(), 'departments');
        }


    }




}