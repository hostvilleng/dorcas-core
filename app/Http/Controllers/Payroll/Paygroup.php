<?php

namespace App\Http\Controllers\Payroll;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollAllowances;
use App\Models\PayrollPaygroup;
use App\Transformers\EmployeeTransformer;
use App\Transformers\PayrollAllowancesTransformer;
use App\Transformers\PayrollGroupTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Paygroup extends Controller
{
    private $paygroup;
    public function __construct(PayrollPaygroup $payGroups)
    {
        $this->paygroup = $payGroups;
    }

    protected $updateFields = [
        'group_name' => 'group_name',
    ];

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $id = $request->input('id');
        # account we want to filter by
        $pagingAppends = ['limit' => $limit];
        $builder = $company->paygroup();
        # append values for the paginator

        $paginator = $builder->when($search, function ($query) use ($search) {
            return $query->where('group_name', 'like', '%'.$search.'%');
        })
            ->oldest('group_name')
            ->paginate($limit);


        $resource = new Collection($paginator->getCollection(), new PayrollGroupTransformer());

        # create the resource
        if (!empty($search)) {
            $pagingAppends['search'] = $search;
            # append the search term to the paginator
            $resource->setMetaValue('search', $search);
            # set the meta value if necessary
        }
        $paginator->appends($pagingAppends);
        # add the append terms
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Manager $fractal)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'group_name' => 'required|max:80',
        ]);
        # validate the request
        $group = $company->paygroup()->create([
            'group_name' => $request->input('group_name'),
        ]);
        # create the model
        $resource = new Item($group, new PayrollGroupTransformer(), 'paygroups');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }


    /**
     * @param PayrollPaygroup $group
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($group, new PayrollGroupTransformer(), 'paygroup');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'group_name' => 'required|max:80',
        ]);
        # validate the request
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $this->updateModelAttributes($group, $request);
        # update the attributes
        $group->saveOrFail();
        # save the changes
        $resource = new Item($group, new PayrollGroupTransformer(), 'paygroup');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $group)->delete()) {
            throw new DeletingFailedException('Failed while deleting the paygroup');
        }
        $resource = new Item($group, new PayrollGroupTransformer(), 'paygroup');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }




    public function employees(Request $request, Manager $fractal, string $id)
    {
        $limit = $request->query('limit', 10);
        # the maximum number of employees to return
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();


        # try to get the group
        $paginator = $group->employees()->oldest('firstname')->oldest('lastname')->paginate($limit);
        # get the employees
        $paginator->appends(['limit' => $limit]);
        # add the append terms
        $resource = new Collection($paginator->getCollection(), new EmployeeTransformer(), 'employee');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function addEmployee(Request $request,Manager $fractal, string $id){
//        return $request->all();
        $this->validate($request, [
            'employee' => 'required_without:employees|string',
            'employees' => 'required_without:employee|array',
            'employees.*' => 'string'
        ]);
        if ($request->has('employees')) {
            $employees = $request->input('employees');
        } else {
            $employees = [$request->input('employee')];
        }
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        $listing = $company->employees()->whereIn('uuid', $employees)
            ->whereNotIn('id', function ($query) use ($group) {
                $query->select('employee_id')
                    ->from('employee_payroll_paygroup')
                    ->where('payroll_paygroup_id', $group->id);
            })
            ->get();

        $group->employees()->attach($listing->pluck('id'));
        # attach these employees
        $resource = new Collection($listing, new EmployeeTransformer(), 'employee');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    public function removeEmployees(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'employee' => 'required_without:employees|string',
            'employees' => 'required_without:employee|array',
            'employees.*' => 'string'
        ]);
        # validate the request
        if ($request->has('employees')) {
            $employees = $request->input('employees');
        } else {
            $employees = [$request->input('employee')];
        }
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $listing = $company->employees()->whereIn('uuid', $employees)->pluck('id');
        # get the matching employees
        $group->employees()->detach($listing);
        # attach these employees
        $paginator = $group->employees()->oldest('firstname')->oldest('lastname')->paginate(10);
        # get the employees
        $resource = new Collection($paginator->getCollection(), new EmployeeTransformer(), 'employee');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }



    public function allowances(Request $request, Manager $fractal, string $id){
        $limit = $request->query('limit', 10);
        # the maximum number of employees to return
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $paginator = $group->allowances()->oldest('allowance_name')->paginate($limit);
        # get the employees

        $paginator->appends(['limit' => $limit]);
        # add the append terms
        $resource = new Collection($paginator->getCollection(), new PayrollAllowancesTransformer(), 'allowance');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function addAllowance(Request $request, Manager $fractal, string $id){
        $this->validate($request,[
            'allowance'=> 'required_without:allowances|string',
            'allowances' => 'required_without:allowance|array',
            'allowances.*' => 'string'
        ]);

        if($request->has('allowances')){
            $allowances = $request->input('allowances');
        }else{
            $allowances = $request->input('allowance');
        }
        $company = $this->company($request);
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        $listing = $company->PayrollAllowances()->whereIn('uuid', $allowances)
            ->whereNotIn('id', function ($query) use ($group) {
                $query->select('payroll_allowances_id')
                    ->from('allowance_payroll_paygroup')
                    ->where('payroll_paygroup_id', $group->id);
            })
            ->get();

        $group->allowances()->attach($listing->pluck('id'));
        # attach these employees
        $resource = new Collection($listing, new PayrollAllowancesTransformer(), 'allowances');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);

    }


    public function removeAllowances(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'allowance' => 'required_without:allowances|string',
            'allowances' => 'required_without:allowance|array',
            'allowances.*' => 'string'
        ]);
        # validate the request
        if ($request->has('allowances')) {
            $allowances = $request->input('allowances');
        } else {
            $allowances = [$request->input('allowance')];
        }
        $company = $this->company($request);
        # retrieve the company
        $group = $company->paygroup()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $listing = $company->PayrollAllowances()->whereIn('uuid', $allowances)->pluck('id');
        # get the matching allowances
        $group->allowances()->detach($listing);
        # attach these allowance
        $paginator = $group->allowances()->oldest('allowance_name')->paginate(10);
        # get the allowances
        $resource = new Collection($paginator->getCollection(), new PayrollAllowancesTransformer(), 'allowances');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }


}