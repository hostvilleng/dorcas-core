<?php

namespace App\Http\Controllers\Crm\Groups;


use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Transformers\GroupTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Groups extends Controller
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
        # try to get the company
        if (!empty($search)) {
            # there's a search term in the URL
            $groups = Group::search($search)->where('company_id', $company->id)
                                            ->orderBy('name', 'asc')
                                            ->get();
            # get the groups
            $resource = new Collection($groups, new GroupTransformer(), 'group');
        } else {
            $paginator = $company->groups()->oldest('name')->paginate($limit);
            # get the customers
            $paginator->appends(['limit' => $limit]);
            # add the append terms
            $resource = new Collection($paginator->getCollection(), new GroupTransformer(), 'group');
            # create the resource
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
            # set the paginator
        }
        if (!empty($search)) {
            $resource->setMetaValue('search', $search);
            # set the meta value if necessary
        }
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
            'name' => 'required|max:80',
            'description' => 'nullable|string'
        ]);
        # validate the request
        $group = $company->groups()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);
        # create the model
        $resource = new Item($group, new GroupTransformer(), 'group');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}