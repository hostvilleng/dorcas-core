<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Department;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class DepartmentTransformer extends TransformerAbstract
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
     * @param Department $department
     *
     * @return array
     */
    public function transform(Department $department)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $department->uuid,
            'name' => $department->name,
            'description' => $department->description,
            'created_at' => $department->created_at->toIso8601String(),
            'links' => [
                'self' => url('/departments', [$department->uuid])
            ],
            'counts' => [
                'employees' => !empty($department->employees_count) ? $department->employees_count : $department->employees()->count()
            ]
        ];
    }

    /**
     * @param Department $department
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Department $department)
    {
        return $this->item($department->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param Department    $department
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeEmployees(Department $department, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $department->employees()->take($limit)
                                ->offset($offset)
                                ->oldest('firstname')
                                ->oldest('lastname')
                                ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }

    public function includeLeaveGroups(Department $department, ParamBag $params = null)
    {
        return $this->item($department->leaveGroups, new LeaveGroupsTransformer(),'leavegroups');
//        list($limit, $offset) = parse_fractal_params($params, 0, 10);
//        $groups = $team->leaveGroups()->take($limit)
//            ->offset($offset)
//            ->oldest('group_type')
//            ->get();
//        return $this->collection($groups, new LeaveTypesTransformer(), 'leaveGroups');
    }
}