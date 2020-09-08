<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Transformers\TeamTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CompanyTeams extends Controller
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
            $paginator = $company->teams()->withCount('employees')->oldest('name')->paginate($limit);
        } else {
            # searching for something
            $paginator = Team::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new TeamTransformer(), 'team');
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
            'name' => 'required|string|max:80',
            'description' => 'nullable|string',
        ]);
        # validate the request
        $team = $company->teams()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);
        # create the team
        $resource = new Item($team, new TeamTransformer(), 'team');
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
        $team = $company->teams()->where('uuid', $id)->firstOrFail();
        # get the team
        if (!(clone $team)->delete()) {
            throw new DeletingFailedException('Sorry but the team could not be deleted. Please try again later.');
        }
        $transformer = new TeamTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # this transformer has no includes
        $resource = new Item($team, $transformer, 'team');
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
        $team = $company->teams()->where('uuid', $id)->firstOrFail();
        # get the team
        $resource = new Item($team, new TeamTransformer(), 'team');
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
            'description' => 'nullable|string',
        ]);
        # validate the request
        $team = $company->teams()->where('uuid', $id)->firstOrFail();
        # get the team
        $this->updateModelAttributes($team, $request);
        # update the attributes
        $team->saveOrFail();
        # save the changes
        $resource = new Item($team, new TeamTransformer(), 'team');
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
    public function removeEmployees(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'employees' => 'array|min:1',
            'employees.*' => 'string',
        ]);
        # validate the request
        $team = $company->teams()->where('uuid', $id)->firstOrFail();
        # get the team
        $employeesIds = $company->employees()->whereIn('uuid', $request->input('employees'))->pluck('id');
        # get the matching employee ids
        if (!empty($request->input('employees')) && $employeesIds->count() === 0) {
            throw new \UnexpectedValueException('There are no employees with the provided ids in the company.');
        }
        if (empty($request->input('employees'))) {
            $team->employees()->detach();
            # remove all employees
        } else {
            $team->employees()->detach($employeesIds);
            # detach the employees
        }
        $transformer = new TeamTransformer();
        $transformer->setDefaultIncludes(['employees']);
        $resource = new Item($team, $transformer, 'team');
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
    public function addEmployees(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'employees' => 'array|min:1',
            'employees.*' => 'string',
        ]);
        # validate the request
        $team = $company->teams()->where('uuid', $id)->firstOrFail();
        # get the team
        $employeesIds = $company->employees()->whereIn('uuid', $request->input('employees'))->pluck('id');
        # get the matching employee ids
        if ($employeesIds->count() === 0) {
            throw new \UnexpectedValueException('There are no employees with the provided ids in the company.');
        }
        $team->employees()->attach($employeesIds);
        # attach the employees
        $transformer = new TeamTransformer();
        $transformer->setDefaultIncludes(['employees']);
        $resource = new Item($team, $transformer, 'team');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}