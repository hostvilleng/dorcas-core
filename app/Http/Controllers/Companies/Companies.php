<?php

namespace App\Http\Controllers\Companies;


use App\Http\Controllers\Controller;
use App\Transformers\CompanyTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class Companies extends Controller
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
        $paginator = \App\Models\Company::when($search, function ($query) use ($search) {
                                            return $query->where('name', 'like', '%' . $search . '%')
                                                            ->orWhere('reg_number', 'like', '%' . $search . '%')
                                                            ->orWhere('phone', 'like', '%' . $search . '%')
                                                            ->orWhere('email', 'like', '%' . $search . '%');
                                        })
                                        ->oldest('name')
                                        ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new CompanyTransformer(), 'company');
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
     */
    public function access_search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = \App\Models\Company::when($search, function ($query) use ($search) {
                                            return $query->where('email', '=', $search);
                                        })
                                        ->oldest('name')
                                        ->paginate($limit);

        $resource = new Collection($paginator->getCollection(), new CompanyTransformer(), 'company');
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

}