<?php

namespace App\Http\Controllers\Crm\Deals;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\DealTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Deals extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'name' => 'name',
        'value_currency' => 'value_currency',
        'value_amount' => 'value_amount',
        'note' => 'note',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
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
        $customerUuids = $request->input('customer_ids');
        # comma-separated UUIDs of customers
        $customerIds = [];
        # array of IDs we're interested in
        if (!empty($customerUuids)) {
            $ids = explode(',', $customerUuids);
            $customerIds = $company->customers()->whereIn('uuid', $ids)->pluck('id')->all();
        }
        $paginator = $company->deals()->with(['stages', 'customer'])
                                        ->when($search, function ($query) use ($search) {
                                            return $query->where('name', 'like', '%' . $search . '%');
                                        })
                                        ->when($customerIds, function ($query) use ($customerIds) {
                                            return $query->whereIn('customer_id', $customerIds);
                                        })
                                        ->latest()
                                        ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new DealTransformer(), 'deal');
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
        $this->validate($request, [
            'name' => 'required|max:80',
            'value_currency' => 'nullable|string|size:3',
            'value_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
            'customer_id' => 'required|string'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $customer = $company->customers()->where('uuid', $request->input('customer_id'))->first();
        # get the customer
        if (empty($customer)) {
            throw new RecordNotFoundException('Could not find the customer profile.');
        }
        $deal = $customer->deals()->create([
            'name' => $request->input('name'),
            'value_currency' => $request->input('value_currency', 'NGN'),
            'value_amount' => $request->input('value_amount', 0.00),
            'note' => $request->input('note'),
        ]);
        # create the model
        $resource = new Item($deal, new DealTransformer(), 'deal');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $deal = $company->deals()->where('uuid', $id)->firstOrFail();
        # try to get the deal
        if (!(clone $deal)->delete()) {
            throw new DeletingFailedException('Failed while deleting the deal');
        }
        $transformer = (new DealTransformer())->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($deal, $transformer, 'deal');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function get(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $deal = $company->deals()->where('uuid', $id)->firstOrFail();
        # try to get the deal
        $resource = new Item($deal, new DealTransformer(), 'deal');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'name' => 'nullable|max:80',
            'value_currency' => 'nullable|string|size:3',
            'value_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);
        # validate the request
        $company = $this->company();
        # get the company
        $deal = $company->deals()->where('uuid', $id)->firstOrFail();
        # try to get the deal
        $this->updateModelAttributes($deal, $request);
        # update the attributes
        $deal->saveOrFail();
        # save the changes
        $resource = new Item($deal, new DealTransformer(), 'deal');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}