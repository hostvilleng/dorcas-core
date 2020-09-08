<?php

namespace App\Http\Controllers\Crm\Customer;


use App\Http\Controllers\Controller;
use App\Transformers\ContactFieldTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ContactFields extends Controller
{
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
        $paginator = $company->contactFields()->when($search, function ($query) use ($search) {
                                                return $query->where('name', 'like', $search.'%');
                                            })
                                            ->oldest('name')
                                            ->paginate($limit);
        # get the contact fields
        $resource = new Collection($paginator->getCollection(), new ContactFieldTransformer(), 'contact_field');
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
            'name' => 'required|max:40',
        ]);
        # validate the request
        $field = $company->contactFields()->create(['name' => $request->input('name')]);
        # create the model
        $resource = new Item($field, new ContactFieldTransformer(), 'contact_field');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}