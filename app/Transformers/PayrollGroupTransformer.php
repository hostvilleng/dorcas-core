<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\PayrollPaygroup;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PayrollGroupTransformer extends TransformerAbstract
{
    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'employees','allowances'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param PayrollPaygroup $group
     *
     * @return array
     */
    public function transform(PayrollPaygroup $group)
    {

        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $group->uuid,
            'name' => $group->group_name,
            'employees_count' => $group->employees()->count(),
            'allowances_count' => $group->allowances()->count(),
            'isActive' => $group->isActive,
            'updated_at' => !empty($group->updated_at) ? $group->updated_at->toIso8601String() : null,
            'created_at' => $group->created_at->toIso8601String()
        ];
        return $resource;
    }

    /**
     * @param PayrollPaygroup $group
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(PayrollPaygroup $group)
    {
        return $this->item($group->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param PayrollPaygroup $group
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeEmployees(PayrollPaygroup $group, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $group->employees()->take($limit)
            ->offset($offset)
            ->oldest('firstname')
            ->oldest('lastname')
            ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }

    public function includeAllowances(PayrollPaygroup $group, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $group->allowances()->take($limit)
            ->offset($offset)
            ->oldest('firstname')
            ->oldest('lastname')
            ->get();
        return $this->collection($employees, new PayrollAllowancesTransformer(), 'allowances');
    }



}