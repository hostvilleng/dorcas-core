<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Transformers\LeaveGroupsTransformer;
use App\Transformers\LeaveTypesTransformer;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
class LeaveGroups  extends Controller
{
    protected $updateFields = [
        'group_type' => 'group_type',
        'duration_days' => 'duration_days',
        'duration_term' => 'duration_term',
    ];
    const DURATION_TERM = ['annual'];
    const GROUP_TYPE = ['department','team'];

    private $leaveGroups;
    public function __construct(\App\Models\LeaveGroups $leaveGroups)
    {
        $this->leaveGroups = $leaveGroups;
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
            $paginator = $company->leaveGroups()->latest()
                ->paginate($limit);
        } else {
            # searching for something
            $paginator = \App\Models\LeaveGroups::search($search)
                ->where('company_id', $company->id)
                ->paginate($limit);
        }
        # get the orders
        $resource = new Collection($paginator->getCollection(), new LeaveGroupsTransformer(), 'leaveGroups');
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
            'group_type' => ['required',Rule::in(self::GROUP_TYPE)],
            'group_id' => 'required',
            'duration_days' => 'required',
            'duration_term' => ['required',Rule::in(self::DURATION_TERM)],
            'type' => 'required_without:types|string',
            'types' => 'required_without:type|array',
            'types.*' => 'string'
        ]);
        if ($request->has('types')) {
            $types = $request->input('types');
        } else {
            $types = [$request->input('type')];
        }
        # validate the request
        $group_id = null;
        if($request->input('group_type') === 'team'){
            $group_id  = $company->teams()->where('uuid',$request->group_id)->firstOrFail()->id;
        }
        else{
            $group_id =  $company->departments()->where('uuid',$request->group_id)->firstOrFail()->id;
        }

        if ($company->leaveGroups()->where('group_id',$group_id)->exists()){

            throw new \Mockery\Exception('The Selected Team has already been created for a leave group ');
        }

        $group = $company->leaveGroups()->create([
            'group_type' => $request->group_type,
            'group_id' => $group_id,
            'duration_days' => $request->duration_days,
            'duration_term' => $request->duration_term
        ]);
        $listing = $company->leaveTypes()->whereIn('uuid', $types)
            ->whereNotIn('id', function ($query) use ($group) {
                $query->select('leave_type_id')
                    ->from('leave_group_type')
                    ->where('leave_group_id', $group->id);
            })
            ->get();
        $group->types()->attach($listing->pluck('id'));
        # create th model
        $resource = new Item($group, new LeaveGroupsTransformer(), 'leaveGroups');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveGroups  = $company->leaveGroups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($leaveGroups, new LeaveGroupsTransformer(), 'leaveGroups');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function update(Request $request, Manager $fractal, string $id){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'group_type' => ['required',Rule::in(self::GROUP_TYPE)],
            'group_id' => 'required',
            'duration_days' => 'required',
            'duration_term' => ['required',Rule::in(self::DURATION_TERM)],
        ]);
        if ($request->has('types')) {
            $types = $request->input('types');
        } else {
            $types = [$request->input('type')];
        }
        # validate the request
        $leaveGroups = $company->leaveGroups()->where('uuid', $id)->firstOrFail();

        if($request->input('group_type') === 'team'){
            $group_id  = $company->teams()->where('uuid',$request->group_id)->firstOrFail()->id;
        }
        else{
            $group_id =  $company->departments()->where('uuid',$request->group_id)->firstOrFail()->id;
        }

        if ($company->leaveGroups()->where('group_id',$group_id)->where('id','!=',$leaveGroups->id)->exists()){

            throw new \Mockery\Exception('The Selected Team has already been created for a leave group ');
        }


        # try to get the approval
        $this->updateModelAttributes($leaveGroups, $request);

        $listing = $company->leaveTypes()->whereIn('uuid', $types)
            ->get();
       $types = $listing->pluck('id');
       $leaveGroups->types()->sync($types);
//       foreach ($types as $type){
//           $leaveGroups->types()->(['leave_type_id'=>$type]);
//
//       }
        # update the attributes
        $leaveGroups->group_id = $group_id;
        $leaveGroups->saveOrFail();

        # save the changes
        $resource = new Item($leaveGroups, new LeaveGroupsTransformer(), 'leaveGroups');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function delete(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $leaveGroups = $company->leaveGroups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $leaveGroups)->delete()) {
            throw new DeletingFailedException('Failed while deleting the Leave Type');
        }
        $resource = new Item($leaveGroups, new LeaveGroupsTransformer(), 'leaveGroups');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}