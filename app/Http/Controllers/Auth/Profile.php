<?php

namespace App\Http\Controllers\Auth;


use App\Dorcas\Common\SupportsBankAccounts;
use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccessGrant;
use App\Transformers\UserAccessGrantTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Profile extends Controller
{
    use SupportsBankAccounts;
    
    /**
     * @var array
     */
    protected $updateFields = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'email' => 'email',
        'gender' => 'gender',
        'password' => 'password',
        'token' => 'remember_token',
        'phone' => 'phone',
        'is_partner' => 'is_partner',
        'is_professional' => 'is_professional',
        'is_vendor' => 'is_vendor',
        'is_verified' => 'is_verified',
        'extra_configurations' => 'extra_configurations',
    ];
    
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getModel(Request $request)
    {
        return $request->user();
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal)
    {
        $resource = new Item($request->user(), new UserTransformer(), 'user');
        if (!empty($request->bearerToken())) {
            $company = $request->user()->company;
            $grant = $company->userAccessGrants()->where('access_token', $request->bearerToken())->first();
            if (!empty($grant)) {
                $grantResource = new Item($grant, (new UserAccessGrantTransformer())->setDefaultIncludes(['user']), 'access_grant');
                $resource->setMetaValue('granted_for', $fractal->createData($grantResource)->toArray());
            }
        }
        $scope = $fractal->createData($resource);
        $resource = $scope->toArray();
        return response()->json($resource);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal)
    {
        $user = $request->user();
        # get the user
        $this->validate($request, [
            'firstname' => 'nullable|max:30',
            'lastname' => 'nullable|max:30',
            'email' => 'nullable|email|max:80',
            'token' => 'nullable|max:100',
            'gender' => 'nullable|in:female,male',
            'phone' => 'nullable|numeric',
            'photo' => 'nullable|image|max:4096',
            'is_partner' => 'nullable|numeric',
            'is_professional' => 'nullable|numeric',
            'is_vendor' => 'nullable|numeric',
            'is_verified' => 'nullable|numeric',
            'extra_configurations' => 'nullable|array',
        ]);
        # validate the request
        if ($request->has('email') && $user->email !== $request->input('email') && User::where('email', $request->input('email'))->count() > 0) {
            # someone is already using the email address
            throw new \UnexpectedValueException(
                'The requested email '.$request->input('email'). ' is already in use by another account.'
            );
        }
        $this->updateModelAttributes($user, $request);
        # update the attributes
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        if ($request->has('photo')) {
            if (!empty($user->photo_url)) {
                Storage::disk(config('filesystems.default'))->delete($user->photo_url);
            }
            $user->photo_url = $request->file('photo')->store('profile-photos');
            # update the profile photo
        }
        $user->saveOrFail();
        # save the changes
        $resource = new Item($user, new UserTransformer(), 'user');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * Update the authentication details for the user.
     *
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAuthentication(Request $request, Manager $fractal)
    {
        validate_api_client($request);
        # validate the request
        $this->validate($request, [
            'email' => 'required|email|max:80',
            'token' => 'nullable|max:100',
            'password' => 'nullable'
        ]);
        # validate the request
        $user = User::where('email', $request->email)->firstOrFail();
        # get the user
        if ($request->has('token')) {
            $user->remember_token = $request->input('token');
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->saveOrFail();
        # save the changes
        $resource = new Item($user, new UserTransformer(), 'user');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAccessGrantRequests(Request $request, Manager $fractal)
    {
        $user = $request->user();
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $allowedStatuses = ['accepted', 'pending', 'rejected'];
        $statuses = $request->input('statuses', '');
        if (!empty($statuses)) {
            $statuses = explode(',', $statuses);
            $statuses = collect($statuses)->filter(function ($status) use ($allowedStatuses) {
                return in_array($status, $allowedStatuses);
            })->all();
        }
        $paginator = $user->companyAccessGrants()->with(['user'])
                            ->when($search, function ($query) use ($search) {
                                return $query->whereIn('company_id', function ($query) use ($search) {
                                    $query->select('id')
                                            ->from('companies')
                                            ->where('name', 'like', '%' . $search . '%')
                                            ->orWhere('reg_number', 'like', '%' . $search . '%')
                                            ->orWhere('email', 'like', '%' . $search . '%')
                                            ->orWhere('phone', 'like', '%' . $search . '%');
                                });
                            })
                            ->when($statuses, function ($query) use ($statuses) {
                                return $query->whereIn('status', $statuses);
                            })
                            ->latest()
                            ->paginate($limit);
        $transformer = new UserAccessGrantTransformer();
        $transformer->setDefaultIncludes(['company']);
        $resource = new Collection($paginator->getCollection(), $transformer, 'access_grant');
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
    public function deleteAccessGrantRequest(Request $request, Manager $fractal, string $id)
    {
        $user = $request->user();
        # get the company the user is tied to
        $accessGrant = $user->companyAccessGrants()->where('uuid', $id)->first();
        # get the access grant
        if (empty($accessGrant)) {
            throw new RecordNotFoundException('No matching access grant found.');
        }
        if (!$accessGrant->delete()) {
            throw new DeletingFailedException('Could not remove the access grant request.');
        }
        $resource = new Item($accessGrant, new UserAccessGrantTransformer(), 'access_grant');
        return response()->json($fractal->createData($resource)->toArray());
    }
}
