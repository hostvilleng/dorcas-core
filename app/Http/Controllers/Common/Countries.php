<?php

namespace App\Http\Controllers\Common;


use App\Http\Controllers\Controller;
use App\Transformers\CountryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Countries extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = \App\Models\Country::oldest('name')->paginate($limit);
        } else {
            # searching for something
            $paginator = \App\Models\Country::search($search)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new CountryTransformer(), 'country');
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
     *
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = \App\Models\Country::where('uuid', $id)->firstOrFail();
        # retrieve the company
        $resource = new Item($company, new CountryTransformer(), 'country');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}