<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Transformers\EmployeeTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Ramsey\Uuid\Uuid;

class CompanyEmployees extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'gender' => 'gender',
        'salary_amount' => 'salary_amount',
        'salary_period' => 'salary_period',
        'staff_code' => 'staff_code',
        'job_title' => 'job_title',
        'email' => 'email',
        'phone' => 'phone'
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
            $paginator = $company->employees()->oldest('firstname')
                                                ->oldest('lastname')
                                                ->paginate($limit);
        } else {
            # searching for something
            $paginator = Employee::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new EmployeeTransformer(), 'employee');
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
            'firstname' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'gender' => 'nullable|string|in:female,male',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_period' => 'nullable|string|in:month,year',
            'staff_code' => 'nullable|string|max:30',
            'job_title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|min:11',
            'department' => 'nullable|string',
            'location' => 'nullable|string'
        ]);
        # validate the request
        $hasCheckedUniqueness = false;
        # whether the data has been checked for uniqueness
        if (!$hasCheckedUniqueness && $request->has('staff_code')) {
            $hasCheckedUniqueness = true;
            if ($company->employees()->where('staff_code', $request->input('staff_code'))->count() > 0) {
                throw new \UnexpectedValueException(
                    'An employee with a staff code of '. $request->input('staff_code').' already exists in your records. '.
                    'If you really want to add this new employee, you can skip adding the staff code for now.'
                );
            }
        }
        if (!$hasCheckedUniqueness && $request->has('email')) {
            if ($company->employees()->where('email', $request->input('email'))->count() > 0) {
                throw new \UnexpectedValueException(
                    'An employee with email '. $request->input('email').' already exists in your records. '.
                    'If you really want to add this new employee, you can skip adding the email for now.'
                );
            }
        }
        $data = $request->except(['location', 'department']);
        $data['uuid'] = Uuid::uuid1()->toString();
        # instantiate the model
        if ($request->has('department')) {
            $department = $company->departments()->where('uuid', $request->input('department'))->first();
            if (empty($department)) {
                throw new RecordNotFoundException('We could not find the department with the provided id.');
            }
            $data['department_id'] = $department->id;
        }
        if ($request->has('location')) {
            $location = $company->locations()->where('uuid', $request->input('location'))->first();
            if (empty($location)) {
                throw new RecordNotFoundException('We could not find the location with the provided id.');
            }
            $data['location_id'] = $location->id;
        }
        $employee = $company->employees()->create($data);
        # create the employee
        if (!$employee) {
            throw new \RuntimeException('Failed while saving the employee to the records.');
        }
        $resource = new Item($employee, new EmployeeTransformer(), 'employee');
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
        $employee = $company->employees()->where('uuid', $id)->firstOrFail();
        # get the employee
        if (!(clone $employee)->forceDelete()) {
            throw new DeletingFailedException('Sorry but the employee could not be deleted. Please try again later.');
        }
        $transformer = new EmployeeTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # this transformer has no includes
        $resource = new Item($employee, $transformer, 'employee');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }


    
    public function createBulk(Request $request, Manager $fractal)
    {
        $this->validate($request, [
            'entries' => 'required|array',
            'entries.*.firstname' => 'required|string|max:30',
            'entries.*.lastname' => 'required|string|max:30',
            'entries.*.salary_amount' => 'nullable|numeric|min:0',
            'entries.*.job_title' => 'nullable|string|max:100',
            'entries.*.email' => 'nullable|email|max:150',
            'entries.*.phone' => 'nullable|string|min:11',
        ]);
        # validate the request

        $company = $this->company($request);
        # get the company

        $entryCollection = [];
        $entries = $request->input('entries');
        foreach ($entries as $entryData) {
            $entryCollection[] = [
                'firstname' => data_get($entryData, 'firstname'),
                'lastname' => data_get($entryData, 'lastname'),
                'salary_amount' => data_get($entryData, 'salary_amount',''),
                'job_title' => data_get($entryData, 'job_title', ''),
                'email' => data_get($entryData, 'email'),
                'phone' => data_get($entryData, 'phone')
            ];
        }

        //$createdEntries = $company->employees()->createMany($entryCollection);

        $dbAction = DB::transaction(function () use ($company, $entryCollection) {
            $createdEntries = $company->employees()->createMany($entryCollection);
            return compact('createdEntries');
        });

        # create all the entries
        $resource = new Collection($dbAction["createdEntries"], new EmployeeTransformer(), 'employee');
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
    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $employee = $company->employees()->where('uuid', $id)->firstOrFail();
        # get the employee
        $resource = new Item($employee, new EmployeeTransformer(), 'employee');
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
            'firstname' => 'nullable|string|max:30',
            'lastname' => 'nullable|string|max:30',
            'gender' => 'nullable|string|in:female,male',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_period' => 'nullable|string|in:month,year',
            'staff_code' => 'nullable|string|max:30',
            'job_title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|min:11',
            'department' => 'nullable|string',
            'location' => 'nullable|string'
        ]);
        # validate the request
        $employee = $company->employees()->where('uuid', $id)->firstOrFail();
        # get the employee
        if ($request->has('department')) {
            $department = $company->departments()->where('uuid', $request->input('department'))->first();
            if (empty($department)) {
                throw new RecordNotFoundException('We could not find the department with the provided id.');
            }
            $employee->department_id = $department->id;
        }
        if ($request->has('location')) {
            $location = $company->locations()->where('uuid', $request->input('location'))->first();
            if (empty($location)) {
                throw new RecordNotFoundException('We could not find the location with the provided id.');
            }
            $employee->location_id = $location->id;
        }
        $this->updateModelAttributes($employee, $request);
        # update the attributes
        $employee->saveOrFail();
        # save the changes
        $resource = new Item($employee, new EmployeeTransformer(), 'employee');
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
    public function removeTeams(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'teams' => 'array|min:1',
            'teams.*' => 'string',
        ]);
        # validate the request
        $employee = $company->employees()->where('uuid', $id)->firstOrFail();
        # get the employee
        $teamIds = $company->teams()->whereIn('uuid', $request->input('teams'))->pluck('id');
        # get the matching employee ids
        if ($teamIds->count() === 0 && !empty($request->input('teams'))) {
            throw new \UnexpectedValueException('There are no teams with the provided ids in the company.');
        }
        if (empty($request->input('teams'))) {
            # remove all
            $employee->teams()->sync([]);
        } else {
            $employee->teams()->detach($teamIds);
        }
        # updates the relationships
        $transformer = new EmployeeTransformer();
        $transformer->setDefaultIncludes(['teams']);
        $resource = new Item($employee, $transformer, 'employee');
        # get the resource
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
    public function addTeams(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request, true);
        # get the company
        $this->validate($request, [
            'teams' => 'array|min:1',
            'teams.*' => 'string',
        ]);
        # validate the request
        $employee = $company->employees()->where('uuid', $id)->firstOrFail();
        # get the employee
        $teamIds = $company->teams()->whereIn('uuid', $request->input('teams'))->pluck('id');
        # get the matching employee ids
        if ($teamIds->count() === 0) {
            throw new \UnexpectedValueException('There are no teams with the provided ids in the company.');
        }
        $employee->teams()->attach($teamIds);
        # updates the relationships
        $transformer = new EmployeeTransformer();
        $transformer->setDefaultIncludes(['teams']);
        $resource = new Item($employee, $transformer, 'employee');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}