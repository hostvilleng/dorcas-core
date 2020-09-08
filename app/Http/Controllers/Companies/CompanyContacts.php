<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\ContactTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CompanyContacts extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'type' => 'type',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'email' => 'email',
        'phone' => 'phone',
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
        $userId = $request->query('user_id');
        # check for a specific user id
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $user = null;
        if (!empty($userId)) {
            $user = User::where('uuid', $userId)->first();
            # get the user
        }
        $paginator = $company->contacts()->when($search, function ($query) use ($search) {
                                                return $query->where('firstname', 'like', '%'.$search.'%')
                                                                ->orWhere('lastname', 'like', '%'.$search.'%')
                                                                ->orWhere('email', 'like', '%'.$search.'%')
                                                                ->orWhere('phone', 'like', '%'.$search.'%');
                                            })
                                            ->when($user, function ($query) use ($user) {
                                                return $query->where('contactable_type', $user->getMorphClass())
                                                                ->where('contactable_id', $user->id);
                                            })
                                            ->oldest()
                                            ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new ContactTransformer(), 'contact');
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
            'user_id' => 'required_without:firstname|string',
            'type' => 'nullable|string|max:80',
            'firstname' => 'required_without:user_id|string|max:30',
            'lastname' => 'nullable|string|max:30',
            'email' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
        ]);
        # validate the request
        $contact = null;
        $data = $request->only(['firstname', 'lastname', 'email', 'phone']);
        $data['type'] = $request->input('type', 'vendor');
        # set the data
        if ($request->has('user_id')) {
            $user = User::where('uuid', $request->input('user_id'))->first();
            if (empty($user)) {
                throw new RecordNotFoundException('Could not find the user.');
            }
            $data['contactable_type'] = $user->getMorphClass();
            $data['contactable_id'] = $user->id;
        }
        $contact = $company->contacts()->create($data);
        # create the contact
        $resource = new Item($contact, new ContactTransformer(), 'contact');
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
        $contact = $company->contacts()->where('uuid', $id)->firstOrFail();
        # get the contact
        if (!(clone $contact)->delete()) {
            throw new DeletingFailedException('Sorry but the contact could not be deleted. Please try again later.');
        }
        $transformer = new ContactTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # this transformer has no includes
        $resource = new Item($contact, $transformer, 'contact');
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
        $contact = $company->contacts()->where('uuid', $id)->firstOrFail();
        # get the contact
        $resource = new Item($contact, new ContactTransformer(), 'contact');
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
            'type' => 'nullable|string|max:80',
            'firstname' => 'nullable|string|max:30',
            'lastname' => 'nullable|string|max:30',
            'email' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
        ]);
        # validate the request
        $contact = $company->contacts()->where('uuid', $id)->firstOrFail();
        # get the contact
        $this->updateModelAttributes($contact, $request);
        # update the attributes
        $contact->saveOrFail();
        # save the changes
        $resource = new Item($contact, new ContactTransformer(), 'contact');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}