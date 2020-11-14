<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Transformers\DepartmentTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CompanyDepartments extends Controller
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
            $paginator = $company->departments()->withCount('employees')->oldest('name')->paginate($limit);
        } else {
            # searching for something
            $paginator = Department::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new DepartmentTransformer(), 'department');
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
        $department = $company->departments()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);
        # create the department
        $resource = new Item($department, new DepartmentTransformer(), 'department');
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
        $department = $company->departments()->where('uuid', $id)->firstOrFail();
        # get the department
        if (!(clone $department)->delete()) {
            throw new DeletingFailedException('Sorry but the department could not be deleted. Please try again later.');
        }
        $transformer = new DepartmentTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # this transformer has no includes
        $resource = new Item($department, $transformer, 'department');
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
        $department = $company->departments()->where('uuid', $id)->firstOrFail();
        # get the department
        $resource = new Item($department, new DepartmentTransformer(), 'department');
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
        $department = $company->departments()->where('uuid', $id)->firstOrFail();
        # get the department
        $this->updateModelAttributes($department, $request);
        # update the attributes
        $department->saveOrFail();
        # save the changes
        $resource = new Item($department, new DepartmentTransformer(), 'department');
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
        $department = $company->departments()->where('uuid', $id)->firstOrFail();
        # get the department
        $employeesIds = $company->employees()->whereIn('uuid', $request->input('employees'))->pluck('id');
        # get the matching employee ids
        if (!empty($request->input('employees')) && $employeesIds->count() === 0) {
            throw new \UnexpectedValueException('There are no employees with the provided ids in the company.');
        }
        if (empty($request->input('employees'))) {
            $department->employees()->dissociate();
            # remove all employees
        } else {
            $builder = Employee::whereIn('id', $employeesIds)->where('department_id', $department->id);
            # create a query builder for this check
            if (!$builder->update(['department_id' => null])) {
                throw new \RuntimeException('Failed while removing the departments for the selected employees.');
            }
        }
        $transformer = new DepartmentTransformer();
        $transformer->setDefaultIncludes(['employees']);
        $resource = new Item($department, $transformer, 'department');
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
        $department = $company->departments()->where('uuid', $id)->firstOrFail();
        # get the department
        $employeesIds = $company->employees()->whereIn('uuid', $request->input('employees'))->pluck('id');
        # get the matching employee ids
        if ($employeesIds->count() === 0) {
            throw new \UnexpectedValueException('There are no employees with the provided ids in the company.');
        }
        if (!Employee::whereIn('id', $employeesIds)->update(['department_id' => $department->id])) {
            throw new \RuntimeException('Failed while updating the departments for the selected employees.');
        }
        # updates the relationships
        $transformer = new DepartmentTransformer();
        $transformer->setDefaultIncludes(['employees']);
        $resource = new Item($department, $transformer, 'department');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}