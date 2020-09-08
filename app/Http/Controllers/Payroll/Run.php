<?php

namespace App\Http\Controllers\Payroll;


use App\Http\Controllers\Controller;
use App\Jobs\People\PeoplePayrollRun;
use App\Models\Employee;
use App\Models\PayrollAllowances;
use App\Models\PayrollPaygroup;
use App\Models\PayrollRun;
use App\Models\PayrollRunAuthorities;
use App\Models\PayrollRunHistories;
use App\Transformers\EmployeeTransformer;
use App\Transformers\PayrollAllowancesTransformer;
use App\Transformers\PayrollGroupTransformer;
use App\Transformers\PayrollRunTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Run extends Controller
{
    private $run;
    public function __construct(PayrollRun $run)
    {
        $this->run = $run;
    }

    CONST STATUS = ['draft','approved','processed'];

    protected $updateFields = [
        'title' => 'title',
        'run' => 'run',
        'status' => 'status',
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
        # the maximum number of runs to return
        $company = $this->company($request);
        # retrieve the company
        $id = $request->input('id');
        # run we want to filter by
        $pagingAppends = ['limit' => $limit];
        $builder = $company->PayrollRun();
        # append values for the paginator

        $paginator = $builder->when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%'.$search.'%');
        })
            ->oldest('title')
            ->paginate($limit);


        $resource = new Collection($paginator->getCollection(), new PayrollRunTransformer());

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
            'title' => 'required|string',
            'run' => 'required|string',
        ]);
        # validate the request
        $run = $company->PayrollRun()->create([
            'title' => $request->input('title'),
            'run' => $request->input('run'),
            'status' => $request->input('status'),
        ]);
        $this->addrunHistory($company,$request,$run);
        # create the model
        $resource = new Item($run, new PayrollRunTransformer(), 'PayrollRun');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }



    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $run = $company->PayrollRun()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($run, new PayrollRunTransformer(), 'payrollRun');
        # get the resource
        return  response()->json($fractal->createData($resource)->toArray());
    }

    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'title' => 'required|string',
            'run' => 'required|string',
            'status' =>['required',Rule::in(self::STATUS),],
        ]);
        if ($request->has('employees')) {
            $employees = $request->input('employees');
        } else {
            $employees = [$request->input('employee')];
        }

        # validate the request
        $run = $company->PayrollRun()->where('uuid', $id)->firstOrFail();

        # update the attributes
        $this->updateModelAttributes($run, $request);

        $employees = $company->employees()->whereIn('uuid',$employees)->pluck('id');

        // Appends The New Employees to the pivot table for selected run id
        $run->employees()->sync($employees);

        //update status of run for all the employees
        foreach ($employees as $employee){
            $run->employees()->updateExistingPivot($employee,['status' => $request->status]);
        }

        // Dispatch Payroll Job if status = 'approved'
        if($request->status === 'approved'){
            Queue::push(new PeoplePayrollRun($run));
        }

        # saves the updated tax run
        $run->saveOrFail();
        # save the changes
        $resource = new Item($run, new PayrollRunTransformer(), 'PayrollRun');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }


    private function addrunHistory($company,Request $request, $run){
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

        $histories= $company->employees()->whereIn('uuid',$employees)
            ->whereNotIn('id',function ($query) use ($run) {
                $query->select('employee_id')
                    ->from('payroll_run_histories')
                    ->where('run_id', $run->id);
            })
            ->get();

        $run->employees()->attach($histories->pluck('id'),['status_data'=> null]);

        return;
    }



    public function delete(Request $request, Manager $fractal, string $id)
    {
        # retrieve the company
        $company = $this->company();

        $run = $company->PayrollRun()->where('uuid', $id)->firstOrFail();
        # try to get the run
        if (!(clone $run)->delete()) {
            throw new DeletingFailedException('Failed while deleting the payroll Run');
        }
        $resource = new Item($run, new PayrollRunTransformer(), 'PayrollRun');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }


    public function getRunEmployeeInvoices(string $id){

        $company = $this->company();
        $run = $this->run->where('uuid', $id)->firstOrFail();
        $processed_employees = DB::table('payroll_run_employees')
            ->join('employees','employees.id','payroll_run_employees.employee_id')
            ->join('payroll_runs','payroll_runs.id','payroll_run_employees.run_id')
            ->where('payroll_runs.id',$run->id)
            ->where('payroll_runs.status','processed')
            ->select('*')
            ->get();
        return response()->json(($processed_employees)->toArray(), 200);

    }

    public function getRunAuthorities(string $id){
        $company = $this->company();
        $run = $this->run->where('uuid',$id)->firstorFail();
        $processed_authorities = DB::table('payroll_run_authorities')
            ->join('payroll_runs','payroll_runs.id','payroll_run_authorities.run_id')
            ->join('payroll_authorities','payroll_authorities.id','payroll_run_authorities.authority_id')
            ->select(DB::raw('SUM(payroll_run_authorities.amount) AS total_amount'),
                'payroll_authorities.authority_name' ,'payroll_authorities.default_payment_details')
            ->where('payroll_runs.id',$run->id)
            ->where('payroll_runs.status','processed')
            ->groupBy('payroll_authorities.id')
            ->get();
        return response()->json(($processed_authorities)->toArray(), 200);
    }



}
