<?php

namespace App\Http\Controllers;


use App\Dorcas\Enum\RoleName;
use App\Events\Invites\InviteCreated;
use App\Exceptions\AccountExistsException;
use App\Exceptions\DeletingFailedException;
use App\Models\Company;
use App\Models\Invite;
use App\Models\Partner;
use App\Models\User;
use App\Transformers\CompanyTransformer;
use App\Transformers\InviteTransformer;
use App\Transformers\PartnerTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Partners extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'name' => 'name',
        'logo_url' => 'logo_url',
        'extra_data' => 'extra_data',
    ];
    
    /**
     *
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
        $paginator = Partner::when($search, function ($query) use ($search) {
                                return $query->where('name', 'like', '%'.$search.'%')
                                                ->orWhere('slug', 'like', '%'.$search.'%');
                            })
                            ->oldest('name')
                            ->paginate($limit);
        # get the partners
        $resource = new Collection($paginator->getCollection(), new PartnerTransformer(), 'partner');
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
    public function create(Request $request, Manager $fractal)
    {
        $this->validate($request, [
            'name' => 'required|string|max:80',
            'logo_url' => 'nullable|string',
            'is_verified' => 'nullable|numeric|in:0,1',
            'extra_data' => 'nullable|array',
        ]);
        # validate the request
        $partner = Partner::create([
            'name' => $request->input('name'),
            'logo_url' => $request->input('logo_url'),
            'extra_data' => $request->input('extra_data', []),
            'is_verified' => $request->input('is_verified', 0)
        ]);
        # try to get the customer
        $resource = new Item($partner, new PartnerTransformer(), 'partner');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function single(Request $request, Manager $fractal, string $id)
    {
        $column = strlen($id) === 36 && substr_count($id, '-') === 4 ? 'uuid' : 'slug';
        # we determine the column to search by
        $partner = Partner::where($column, $id)->firstOrFail();
        # retrieve the partner
        $resource = new Item($partner, new PartnerTransformer(), 'partner');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'name' => 'nullable|string|max:80',
            'logo_url' => 'logo_url|string',
            'extra_data' => 'nullable|array',
            'logo' => 'nullable|image|max:4096'
        ]);
        # validate the request
        $allowedColumns = ['uuid', 'slug'];
        $column = $request->input('select_using', 'uuid');
        # we determine the column to search by
        $column = in_array($column, $allowedColumns) ? $column : 'uuid';
        # normalise it
        $partner = Partner::where($column, $id)->firstOrFail();
        # retrieve the partner
        if ($request->has('logo')) {
            # we have a logo
            if (!empty($partner->logo_url) && !starts_with($partner->logo_url, 'http')) {
                # we delete the previous one
                Storage::disk(config('filesystems.default'))->delete($partner->logo_url);
            }
            $path = $request->file('logo')->store('partner-' . $partner->id);
            # save the logo
            $request->request->set('logo_url', $path);
        }
        $this->updateModelAttributes($partner, $request);
        # update the value
        $partner->saveOrFail();
        # commit the changes
        $resource = new Item($partner, new PartnerTransformer(), 'partner');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCompanies(Request $request, Manager $fractal)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the pagination
        $paginator = Company::withTrashed()->whereIn('id', function ($query) use ($partner) {
                                                return $query->select('company_id')
                                                            ->from('users')
                                                            ->where('partner_id', $partner->id);
                                            })
                                            ->when($search, function ($query) use ($search) {
                                                return $query->where(function ($query) use ($search) {
                                                    $query->where('name', 'like', '%'.$search.'%')
                                                            ->orWhere('reg_number', 'like', '%'.$search.'%')
                                                            ->orWhere('phone', 'like', '%'.$search.'%')
                                                            ->orWhere('email', 'like', '%'.$search.'%')
                                                            ->orWhere('website', 'like', '%'.$search.'%');
                                                });
                                            })
                                            ->oldest('name')
                                            ->paginate($limit);
        # get the paginator
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
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCompany(Request $request, Manager $fractal, string $id)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $company = Company::withTrashed()
                            ->whereIn('id', function ($query) use ($partner) {
                                return $query->select('company_id')
                                    ->from('users')
                                    ->where('partner_id', $partner->id);
                            })
                            ->where('uuid', $id)
                            ->firstOrFail();
        # find the company
        $method = $request->has('purge') ? 'forceDelete' : 'delete';
        if (!(clone $company)->{$method}()) {
            throw new DeletingFailedException('Could not delete the selected company.');
        }
        $transformer = new CompanyTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($company, $transformer, 'company');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request, Manager $fractal)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $listing = $request->input('listing', 'members');
        # the listing to look at
        $company = null;
        if ($request->has('company_id')) {
            $company = Company::where('uuid', $request->input('company_id'))->first();
        }
        if (empty($search)) {
            # no search parameter
            switch(strtolower($listing)) {
                case 'managers':
                    $builder = $partner->administrators();
                    break;
                default:
                    $builder = User::withTrashed()->with([
                                                        'company' => function ($query) {
                                                            $query->withTrashed();
                                                        }
                                                    ])
                                                    ->where('partner_id', $partner->id);
            }
            $paginator = $builder->when($company, function ($query) use ($company) {
                                        return $query->where('company_id', $company->id);
                                    })
                                    ->oldest('firstname')
                                    ->oldest('lastname')
                                    ->paginate($limit);
            
        } else {
            # searching for something
            switch(strtolower($listing)) {
                case 'managers':
                    $paginator = $partner->administrators()->where(function ($query) use ($search) {
                                                                $query->where('firstname', 'like', '%'.$search.'%')
                                                                        ->orWhere('lastname', 'like', '%'.$search.'%')
                                                                        ->orWhere('phone', 'like', '%'.$search.'%')
                                                                        ->orWhere('email', 'like', '%'.$search.'%');
                                                            })
                                                            ->orderBy('firstname')
                                                            ->orderBy('lastname')
                                                            ->paginate($limit);
                    break;
                default:
                    $builder = User::search($search)->withTrashed()
                                                        ->where('partner_id', $partner->id)
                                                        ->orderBy('firstname')
                                                        ->orderBy('lastname');
                    if (!empty($company)) {
                        $builder->where('company_id', $company->id);
                    }
                    $paginator = $builder->paginate($limit);
            }
        }
        # get the users
        $transformer = new UserTransformer();
        $transformer->setDefaultIncludes(['company', 'roles']);
        $resource = new Collection($paginator->getCollection(), $transformer, 'user');
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
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request, Manager $fractal, string $id)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $user = User::where('partner_id', $partner->id)->where('uuid', $id)->firstOrFail();
        # find the user
        if (!(clone $user)->delete()) {
            throw new DeletingFailedException('Could not delete the selected user.');
        }
        $transformer = new UserTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($user, $transformer, 'user');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sentInvites(Request $request, Manager $fractal)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the pagination
        $filters = $request->input('filters');
        # the available filters -- comma separated list of the filters to enable
        if (!empty($filters) && is_string($filters)) {
            $filters = explode(',', $filters);
            # explode the filter to get the keys
        }
        $paginator = Invite::when($search, function ($query) use ($search) {
                                return $query->where(function ($query) use ($search) {
                                    $query->where('email', 'like', '%'.$search.'%')
                                            ->orWhere('firstname', 'like', '%'.$search.'%')
                                            ->orWhere('lastname', 'like', '%'.$search.'%')
                                            ->orWhere('status', 'like', '%'.$search.'%');
                                });
                            })
                            ->when($filters, function ($query) use ($filters) {
                                return $query->whereIn('status', $filters);
                            })
                            ->where('inviter_type', (new Partner())->getMorphClass())
                            ->where('inviter_id', $partner->id)
                            ->latest()
                            ->paginate($limit);
        # get the paginator
        $resource = new Collection($paginator->getCollection(), new InviteTransformer(), 'invite');
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
    public function invite(Request $request, Manager $fractal)
    {
        $this->validate($request, [
            'business' => 'nullable|string',
            'email' => 'required|email',
            'firstname' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'roles' => 'nullable|array',
            'roles.*' => 'required_with:roles|string'
        ]);
        # validate the request
        if (User::where('email', $request->input('email'))->count() > 0) {
            throw new AccountExistsException(
                'An account with the email "' . $request->input('email') . '" already exists.'
            );
        }
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $properties = ['is_verified' => 1];
        $roles = [];
        if (!$request->has('business')) {
            # inviting a manager
            $roles[] = RoleName::PARTNER;
            $config = [
                'action' => 'invite_user',
                'roles' => collect($roles)->unique()->all(),
                'properties' => $properties,
                'inviting_user_id' => $request->user()->id,
            ];
            
        } else {
            $config = [
                'action' => 'invite_business',
                'business' => $request->input('business'),
                'roles' => collect($roles)->unique()->all(),
                'properties' => $properties,
                'inviting_user_id' => $request->user()->id
            ];
        }
        $invite = $partner->invites()->create([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'message' => $request->input('message', ''),
            'config_data' => $config
        ]);
        # create the invite
        event(new InviteCreated($invite));
        # trigger the event
        $resource = new Item($invite, new InviteTransformer(), 'invite');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteInvite(Request $request, Manager $fractal, string $id)
    {
        $partner = $request->user()->partner;
        # get the partner the user is tied to
        $invite = Invite::where('inviter_type', (new Partner)->getMorphClass())
                        ->where('inviter_id', $partner->id)
                        ->where('uuid', $id)
                        ->first();
        # get the invite
        if (!(clone $invite)->delete()) {
            throw new DeletingFailedException('Errors while deleting the invite.');
        }
        $transformer = new InviteTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($invite, $transformer, 'invite');
        return response()->json($fractal->createData($resource)->toArray());
    }
}