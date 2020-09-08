<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Team;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class TeamTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'employees','leavegroups'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Team $team
     *
     * @return array
     */
    public function transform(Team $team)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $team->uuid,
            'name' => $team->name,
            'description' => $team->description,
            'created_at' => $team->created_at->toIso8601String(),
            'links' => [
                'self' => url('/teams', [$team->uuid])
            ],
            'counts' => [
                'employees' => !empty($team->employees_count) ? $team->employees_count : $team->employees()->count()
            ]
        ];
    }

    /**
     * @param Team $team
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Team $team)
    {
        return $this->item($team->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param Team          $team
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeEmployees(Team $team, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $team->employees()->take($limit)
                                            ->offset($offset)
                                            ->oldest('firstname')
                                            ->oldest('lastname')
                                            ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }


    public function includeLeaveGroups(Team $team, ParamBag $params = null)
    {
        return $this->item($team->groups, new LeaveGroupsTransformer(),'leavegroups');
//        list($limit, $offset) = parse_fractal_params($params, 0, 10);
//        $groups = $team->leaveGroups()->take($limit)
//            ->offset($offset)
//            ->oldest('group_type')
//            ->get();
//        return $this->collection($groups, new LeaveTypesTransformer(), 'leaveGroups');
    }
}