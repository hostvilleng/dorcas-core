<?php

namespace App\Http\Controllers\Approval;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Approvals;
use App\Transformers\ApprovalsTransformer;
use App\Transformers\ApprovalTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class Approval extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'title' => 'title',
        'scope_type' => 'scope_type',
        'active' => 'active',
        'frequency_type' => 'frequency_type',
    ];
    const SCOPE = ['key_person','min_number','both'];
    const FREQUENCY = ['sequential','random'];


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
            $paginator = $company->approvals()->latest()->paginate($limit);
        } else {
            # searching for something
            $paginator = \App\Models\Approvals::when($search, function ($query) use ($search,$company) {
                return $query->where('company_id',$company->id)
                    ->where('title', 'like', '%'.$search.'%');
            })
                ->oldest('title')
                ->paginate($limit);
//            $paginator = \App\Models\Approvals::search($search)
//                                                ->where('company_id', $company->id)
//                                                ->paginate($limit);
        }
        # get the orders
        $resource = new Collection($paginator->getCollection(), new ApprovalsTransformer(), 'approval');
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
            'title' => 'required|max:80',
            'scope_type' => ['required',Rule::in(self::SCOPE),],
            'frequency_type' => ['required',Rule::in(self::FREQUENCY),]
        ]);
        # validate the request
        $group = $company->approvals()->create([
            'title' => $request->input('title'),
            'scope_type' => $request->input('scope_type'),
            'frequency_type' => $request->input('frequency_type'),
        ]);
        # create the model
        $resource = new Item($group, new ApprovalsTransformer(), 'approval');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $approval = $company->approvals()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($approval, new ApprovalsTransformer(), 'approvals');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function update(Request $request, Manager $fractal, string $id){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'title' => 'required|max:80',
            'scope_type' => ['required',Rule::in(self::SCOPE),],
            'frequency_type' => ['required',Rule::in(self::FREQUENCY),]

        ]);
        # validate the request
        $approval = $company->approvals()->where('uuid', $id)->firstOrFail();
        # try to get the approval
        $this->updateModelAttributes($approval, $request);
        # update the attributes
        $approval->saveOrFail();
        # save the changes
        $resource = new Item($approval, new ApprovalsTransformer(), 'approval');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function delete(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $approval = $company->approvals()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $approval)->delete()) {
            throw new DeletingFailedException('Failed while deleting the Approval');
        }
        $resource = new Item($approval, new ApprovalsTransformer(), 'approvals');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }


}