<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\State;
use App\Transformers\LocationTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CompanyLocations extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'name' => 'name',
        'address1' => 'address1',
        'address2' => 'address2',
        'city' => 'city'
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
        $company = $this->company($request, true);
        # get the company
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = $company->locations()->withCount('employees')->oldest('address1')->oldest('address2')->paginate($limit);
        } else {
            # searching for something
            $paginator = Location::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new LocationTransformer(), 'location');
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
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'name' => 'nullable|string|max:80',
            'address1' => 'required|string|max:100',
            'address2' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:80',
            'state' => 'required|string|max:50'
        ]);
        # validate the request
        $state = State::where('uuid', $request->input('state'))->first();
        # get the related state
        if (empty($state)) {
            throw new RecordNotFoundException('Sorry, we could not find the state with the provided id');
        }
        $location = $company->locations()->create([
            'name' => $request->input('name', 'Head Office'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'city' => $request->input('city'),
            'state_id' => $state->id
        ]);
        # create the location
        $resource = new Item($location, new LocationTransformer(), 'location');
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
        $company = $this->company($request, true);
        # get the company
        $location = $company->locations()->where('uuid', $id)->firstOrFail();
        # get the location
        if (!(clone $location)->delete()) {
            throw new DeletingFailedException('Sorry but the location could not be deleted. Please try again later.');
        }
        $transformer = new LocationTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # this transformer has no includes
        $resource = new Item($location, $transformer, 'location');
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
    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $location = $company->locations()->where('uuid', $id)->firstOrFail();
        # get the location
        $resource = new Item($location, new LocationTransformer(), 'location');
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
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'name' => 'nullable|string|max:80',
            'address1' => 'nullable|string|max:100',
            'address2' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:80',
            'state' => 'nullable|string|max:50'
        ]);
        # validate the request
        $location = $company->locations()->where('uuid', $id)->firstOrFail();
        # get the location
        $this->updateModelAttributes($location, $request);
        # update the attributes
        if ($request->has('state')) {
            $state = State::where('uuid', $request->input('state'))->first();
            # get the related state
            if (empty($state)) {
                throw new \RuntimeException('Sorry, we could not find the state with the provided id');
            }
            $location->state_id = $state->id;
        }
        $location->saveOrFail();
        # save the changes
        $resource = new Item($location, new LocationTransformer(), 'location');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}