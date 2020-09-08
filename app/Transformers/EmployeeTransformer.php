<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Http\Controllers\Approval\Approval;
use App\Models\Employee;
use App\Models\PayrollPaygroup;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'department', 'location', 'teams', 'user','paygroups','payrollruns'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['department', 'location', 'user','paygroups','payrollruns'];

    /**
     * @param Employee $employee
     *
     * @return array
     */
    public function transform(Employee $employee)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $employee->uuid,
            'job_title' => $employee->job_title,
            'staff_code' => $employee->staff_code,
            'firstname' => $employee->firstname,
            'lastname' => $employee->lastname,
            'gender' => $employee->gender,
            'salary' => [
                'raw' => $employee->salary_amount,
                'formatted' => number_format($employee->salary_amount, 2),
                'period' => $employee->salary_period
            ],
            'photo' => $employee->photo,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'is_trashed' => $employee->deleted_at !== null,
            'trashed_at' => !empty($employee->deleted_at) ? $employee->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($employee->updated_at) ? $employee->updated_at->toIso8601String() : null,
            'created_at' => $employee->created_at->toIso8601String(),
            'links' => [
                'self' => url('/employees', [$employee->uuid])
            ]
        ];
    }

    /**
     * @param Employee $employee
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Employee $employee)
    {
        return $this->item($employee->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param Employee $employee
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeDepartment(Employee $employee)
    {
        if (empty($employee->department_id)) {
            return null;
        }
        return $this->item($employee->department, new DepartmentTransformer(), 'department');
    }

    /**
     * @param Employee $employee
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeLocation(Employee $employee)
    {
        if (empty($employee->location_id)) {
            return null;
        }
        return $this->item($employee->location, new LocationTransformer(), 'location');
    }

    /**
     * @param Employee      $employee
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeTeams(Employee $employee, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $teams = $employee->teams()->take($limit)->offset($offset)->oldest('name')->get();
        return !empty($teams) && $teams->count() > 0 ? $this->collection($teams, new TeamTransformer(), 'team') : null;
    }
    
    /**
     * @param Employee $employee
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeUser(Employee $employee)
    {
        if (empty($employee->user_id)) {
            return null;
        }
        return $this->item($employee->user, new UserTransformer(), 'user');
    }

    /**
     * @param Employee $employee
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includePaygroups(Employee $employee, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $groups = $employee->PayrollGroup()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($groups, new PayrollGroupTransformer(), 'paygroups');
    }

    public function includePayrollTransactions(Employee $employee, ParamBag $params){
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $transactions = $employee->PayrollTransaction()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($transactions, new PayrollTransactionTransformer(), 'payrolltransactions');
    }


    public function includePayrollRuns(Employee $employee, ParamBag $params){
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $runs = $employee->runs()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($runs, new PayrollRunTransformer(), 'payrollruns');
    }

}