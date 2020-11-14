<?php

namespace App\Http\Controllers\Leave;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ApprovalAuthorizers;
use App\Models\Company;
use App\Models\Approvals;
use App\Transformers\ApprovalAuthorizersTransformer;
use App\Transformers\ApprovalRequestsTransformer;
use App\Transformers\ApprovalsTransformer;
use App\Transformers\ApprovalTransformer;
use App\Transformers\LeaveRequestsTransformer;
use App\Transformers\LeaveTypesTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Csv\Exception;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Zend\Validator\Date;

class LeaveRequests extends Controller
{
    protected $updateFields = [
        'count_requesting' => 'count_requesting',
        'data_start_date' => 'data_start_date',
        'data_report_back' => 'data_report_back',
        'data_contact_address' => 'data_contact_address',
        'data_contact_phone' => 'data_contact_phone',
        'data_backup_staff' => 'data_backup_staff',
        'data_remarks' => 'data_remarks',
    ];

    //gets all leave requests for employees
    public function index(Request $request,Manager $fractal){
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        # retrieve the company
        $pagingAppends = ['limit' => $limit];

        $company = $this->company();

        $user = $company->users()->where('uuid',$request->input('user_id'))->firstOrFail();

        $employee_id = $company->employees()->where('user_id',$user->id)->firstOrFail()->id;

        # append values for the paginator
        # searching for something
//        $approval_id = $company->approvals()->where('uuid', $request->query('approval_id'))->firstOrFail()->id;
        if (empty($search)) {
            $paginator = \App\Models\LeaveRequests::where(function ($query) use ($search,$company,$employee_id) {
                return $query->where('company_id',$company->id)
                             ->where('employee_id',$employee_id);
            })
                ->oldest()
                ->paginate($limit);

        }
        else{
            $paginator = \App\Models\LeaveRequests::search($search)
                ->where('company_id', $company->id)
                ->where('employee_id', $employee_id)
                ->paginate($limit);
        }


        # get the orders
        $resource = new Collection($paginator->getCollection(), new LeaveRequestsTransformer(), 'leaveRequests');
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

    public function getEmployeeLeaveTypes(Request $request, Manager $fractal, string $userId){
        $company = $this->company();
        $user = $company->users()->where('uuid',$userId)->firstOrFail();
        $employee = $company->employees()->with('teams')->where('user_id',$user->id)->firstOrFail();
        //get employee team idw
        $teams = collect($employee->teams)->pluck('id');
        $group = $company->leaveGroups()->with('types')->whereIn('group_id',$teams)->firstOrFail();
        $availableLeaveType = collect($group->types)->pluck('uuid');
        $types = $company->leaveTypes()->whereIn('uuid',$availableLeaveType)->oldest()->get();
        $resource = new Collection($types, new LeaveTypesTransformer(), 'leaveTypes');
        return response()->json($fractal->createData($resource)->toArray());

    }

    public function create(Request $request,Manager $fractal){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'user_id' => 'required',
            'type_id' => 'required',
            'count_requesting' => 'required|numeric',
            'data_start_date' => 'required|date_format:Y-m-d',
            'data_contact_address' => 'required',
            'data_contact_phone' => 'required',
            'data_backup_staff' => 'required',
            'data_remarks' => 'required',
        ]);

        //first check ( check if the employee belongs to a team)
        $user = $company->users()->where('uuid',$request->input('user_id'))->firstOrFail();
        $employee = $company->employees()->with('teams')->where('user_id',$user->id)->firstOrFail();
        //get employee team idw
        $teams = collect($employee->teams)->pluck('id');
        $group = $company->leaveGroups()->with('types')->whereIn('group_id',$teams)->firstOrFail();
        $availableLeaveType = collect($group->types)->pluck('uuid');
        $leaveType = $company->leaveTypes()->where('uuid',$request->type_id)->whereIn('uuid',$availableLeaveType)->firstorFail();

        if(isset($leaveType)){
            $count_utilized = $this->getUsedLeave($employee->id);
            $count_remaining = $group->duration_days - $count_utilized;
            if($request->count_requesting <= $count_remaining){
                $start_date =  $request->data_start_date;
                $report_back = $this->calculateReportBack($start_date,$request->count_requesting);
                $leaveRequest = $company->leaveRequests()->create([
                    'group_id' => $group->id,
                    'employee_id' => $employee->id,
                    'approval_id' => $leaveType->approval_id,
                    'type_id' => $leaveType->id,
                    'count_utilized' => $count_utilized,
                    'count_available' => $count_remaining,
                    'count_remaining' => $count_remaining,
                    'count_requesting' => $request->count_requesting,
                    'data_start_date' =>$start_date,
                    'data_report_back' => $report_back,
                    'data_contact_address' => $request->data_contact_address,
                    'data_contact_phone' => $request->data_contact_phone,
                    'data_backup_staff' => $request->data_backup_staff,
                    'data_remarks' => $request->data_remarks,
                ]);
                $company->approvalRequests()->create([
                    'approval_id' => $leaveType->approval_id,
                    'model' => 'App\Models\LeaveRequests',
                    'model_request_id'=> $leaveRequest->id,
                ]);

                $resource = new Item($leaveRequest, new LeaveRequestsTransformer(), 'leaveRequest');
                return response()->json($fractal->createData($resource)->toArray(), 201);
            }
            else{
                throw new \Mockery\Exception('The Amount of Leave days you are requesting for is more than the amount of days you have left ');
            }
        }
        else{
            throw new RecordNotFoundException('Employee Does not Belong to the Group');
        }
    }

    private function getUsedLeave($employee) :int{
        $company = $this->company();
        $builder = $company->leaveRequests()->where('employee_id',$employee)->where('status','!=','declined')->sum('count_requesting');
        return $builder;
    }

    private function calculateReportBack($start_date,$requesting_days)  {
        $dt = Carbon::parse($start_date);
        $report_back = null;
        for ($i=0; $i<=$requesting_days; $i++){
            $dt = $dt->addDay();
            if ($dt->isWeekend()){
                $dt = $dt->next(Carbon::MONDAY);
            }
        }
        return $dt->format('Y-m-d');
    }

    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveRequest  = $company->leaveRequests()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($leaveRequest, new LeaveRequestsTransformer(), 'leaveRequest');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }


    public function update(Request $request, Manager $fractal, string $id){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'user_id' => 'required',
            'type_id' => 'required',
            'count_requesting' => 'required|numeric',
            'data_start_date' => 'required|date_format:Y-m-d',
            'data_contact_address' => 'required',
            'data_contact_phone' => 'required',
            'data_backup_staff' => 'required',
            'data_remarks' => 'required',
        ]);

        //first check ( check if the employee belongs to a group)
        $leaveRequest = $company->leaveRequests()->where('uuid',$id)->firstOrFail();
        $user = $company->users()->where('uuid',$request->input('user_id'))->firstOrFail();
        $employee = $company->employees()->with('teams')->where('user_id',$user->id)->firstOrFail();
        //get employee team idw
        $teams = collect($employee->teams)->pluck('id');
        $group = $company->leaveGroups()->with('types')->whereIn('group_id',$teams)->firstOrFail();
        $availableLeaveType = collect($group->types)->pluck('uuid');
        $leaveType = $company->leaveTypes()->where('uuid',$request->type_id)->whereIn('uuid',$availableLeaveType)->firstorFail();
        if(isset($leaveType)){
            $count_utilized = $this->getUsedLeave($employee->id);
            $count_remaining = $group->duration_days - $count_utilized;
            if($request->count_requesting <= $count_remaining){
                $start_date =  $request->data_start_date;
                $report_back = $this->calculateReportBack($start_date,$request->count_requesting);
                $this->updateModelAttributes($leaveRequest, $request);
                $leaveRequest->count_utilized = $count_utilized;
                $leaveRequest->count_available = $count_remaining;
                $leaveRequest->count_remaining = $count_remaining;
                $leaveRequest->data_report_back = $report_back;
                $leaveRequest->saveOrFail();
                $company->approvalRequests()->create([
                    'approval_id' => $leaveType->approval_id,
                    'model' => 'App\Models\LeaveRequests',
                    'model_request_id'=> $leaveRequest->id,
                ]);


                $resource = new Item($leaveRequest, new LeaveRequestsTransformer(), 'leaveRequest');
                return response()->json($fractal->createData($resource)->toArray(), 201);
            }
            else{
                throw new \Mockery\Exception('The Amount of Leave days you are requesting for is less than the amount of days you have left ');
            }
        }
        else{
            throw new RecordNotFoundException('Employee Does not Belong to the Group');
        }
    }

    public function delete(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveRequest = $company->leaveRequests()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $leaveRequest)->delete()) {
            throw new DeletingFailedException('Failed while deleting the Leave Request');
        }
        $resource = new Item($leaveRequest, new LeaveRequestsTransformer(), 'leaveRequest');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}
