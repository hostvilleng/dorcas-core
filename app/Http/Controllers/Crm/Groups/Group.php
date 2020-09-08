<?php

namespace App\Http\Controllers\Crm\Groups;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Transformers\CustomerTransformer;
use App\Transformers\GroupTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Group extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'name' => 'name',
        'description' => 'description'
    ];
    
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
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $group)->delete()) {
            throw new DeletingFailedException('Failed while deleting the group');
        }
        $resource = new Item($group, new GroupTransformer(), 'group');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     *
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $resource = new Item($group, new GroupTransformer(), 'group');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'name' => 'nullable|max:80',
            'description' => 'nullable|string'
        ]);
        # validate the request
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $this->updateModelAttributes($group, $request);
        # update the attributes
        $group->saveOrFail();
        # save the changes
        $resource = new Item($group, new GroupTransformer(), 'group');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function removeCustomers(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'customer' => 'required_without:customers|string',
            'customers' => 'required_without:customer|array',
            'customers.*' => 'string'
        ]);
        # validate the request
        if ($request->has('customers')) {
            $customers = $request->input('customers');
        } else {
            $customers = [$request->input('customer')];
        }
        $company = $this->company($request);
        # retrieve the company
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $listing = $company->customers()->whereIn('uuid', $customers)->pluck('id');
        # get the matching customers
        $group->customers()->detach($listing);
        # attach these customers
        $paginator = $group->customers()->oldest('firstname')->oldest('lastname')->paginate(10);
        # get the customers
        $resource = new Collection($paginator->getCollection(), new CustomerTransformer(), 'customer');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
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
    public function customers(Request $request, Manager $fractal, string $id)
    {
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $paginator = $group->customers()->oldest('firstname')->oldest('lastname')->paginate($limit);
        # get the customers
        $paginator->appends(['limit' => $limit]);
        # add the append terms
        $resource = new Collection($paginator->getCollection(), new CustomerTransformer(), 'customer');
        # create the resource
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
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
    public function addCustomers(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'customer' => 'required_without:customers|string',
            'customers' => 'required_without:customer|array',
            'customers.*' => 'string'
        ]);
        # validate the request
        if ($request->has('customers')) {
            $customers = $request->input('customers');
        } else {
            $customers = [$request->input('customer')];
        }
        $company = $this->company($request);
        # retrieve the company
        $group = $company->groups()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $listing = $company->customers()->whereIn('uuid', $customers)
                                        ->whereNotIn('id', function ($query) use ($group) {
                                            $query->select('customer_id')
                                                    ->from('customer_group')
                                                    ->where('group_id', $group->id);
                                        })
                                        ->get();
        # get the matching customers
        $group->customers()->attach($listing->pluck('id'));
        # attach these customers
        $resource = new Collection($listing, new CustomerTransformer(), 'customer');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}