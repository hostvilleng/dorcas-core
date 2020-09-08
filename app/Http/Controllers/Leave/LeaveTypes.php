<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Transformers\LeaveTypesTransformer;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
class LeaveTypes  extends Controller
{
    protected $updateFields = [
        'title' => 'title',
    ];

    public function __construct()
    {

    }

    /**
     * @param Request      $request
     * @param Manager      $fractal
     * @param string       $id
     * @param Company|null $company
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company();
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = $company->leaveTypes()->latest()
                ->paginate($limit);
        } else {
            # searching for something
            $paginator = \App\Models\LeaveTypes::search($search)
                ->where('company_id', $company->id)
                ->paginate($limit);
        }
        # get the orders
        $resource = new Collection($paginator->getCollection(), new LeaveTypesTransformer(), 'leaveTypes');
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

    public function create(Request $request,Manager $fractal){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'title' => 'required',
            'approval_id' => 'required',
        ]);
        # validate the request

        $approval = $company->approvals()->where(['uuid'=>$request->approval_id,'active'=>1])->firstOrFail();
        $group = $company->leaveTypes()->create([
            'title' => $request->title,
            'approval_id' => $approval->id
        ]);
        # create the model
        $resource = new Item($group, new LeaveTypesTransformer(), 'leaveTypes');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveTypes  = $company->leaveTypes()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($leaveTypes, new LeaveTypesTransformer(), 'approvals');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function update(Request $request, Manager $fractal, string $id){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'title' => 'required|max:80',
        ]);
        # validate the request
        $leaveTypes = $company->leaveTypes()->where('uuid', $id)->firstOrFail();
        # try to get the approval
        $this->updateModelAttributes($leaveTypes, $request);

        if($request->has('approval_id')){
            $approval = $company->approvals()->where(['uuid'=>$request->approval_id,'active'=>1])->firstOrFail();

            $leaveTypes->approval_id = $approval->id;
        }
        # update the attributes
        $leaveTypes->saveOrFail();
        # save the changes
        $resource = new Item($leaveTypes, new LeaveTypesTransformer(), 'leaveTypes');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function delete(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveTypes = $company->leaveTypes()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $leaveTypes)->delete()) {
            throw new DeletingFailedException('Failed while deleting the Leave Type');
        }
        $resource = new Item($leaveTypes, new LeaveTypesTransformer(), 'leaveTypes');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}