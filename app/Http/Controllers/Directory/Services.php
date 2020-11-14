<?php

namespace App\Http\Controllers\Directory;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalCategory;
use App\Models\ProfessionalService;
use App\Models\VendorService;
use App\Transformers\ProfessionalServiceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Services extends Controller
{
    /** @var array */
    protected $updateFields = [
        'title' => 'title',
        'cost_type' => 'cost_type',
        'cost_frequency' => 'cost_frequency',
        'cost_currency' => 'cost_currency',
        'cost_amount' => 'cost_amount',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $categoryId = $request->query('category_id');
        # get the category id, if specified
        $userId = $request->query('user_id');
        # get the services for a particular user
        $type = $request->query('mode');
        # the types of services to load up
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $category = null;
        # default value for the category
        if (!empty($categoryId)) {
            $category = ProfessionalCategory::where('uuid', $categoryId)->firstOrFail();
            # get the category
        }
        $className = $type === 'vendor' ? VendorService::class : ProfessionalService::class;
        # we set the class to be used
        if (empty($search)) {
            # no search parameter
            $paginator = $className::with(['categories', 'user'])
                                        ->when($category, function ($query) use ($category) {
                                            return $query->whereIn('id', function ($query) use ($category) {
                                                $query->select('professional_service_id')
                                                    ->from('professional_category_services')
                                                    ->where('professional_category_id', $category->id);
                                            });
                                        })
                                        ->when($userId, function ($query) use ($userId) {
                                            return $query->where('user_id', $userId);
                                        })
                                        ->oldest('title')
                                        ->paginate($limit);
            # get the model
        } else {
            # searching for something
            $builder = $className::search($search);
            if ($userId) {
                $builder = $builder->where('user_id', $userId);
            }
            $paginator = $builder->paginate($limit);
            $paginator->getCollection()->load('categories', 'user');
        }
        $transformer = new ProfessionalServiceTransformer();
        $transformer->setDefaultIncludes(['categories', 'user']);
        $resource = new Collection($paginator->getCollection(), $transformer, 'professional_service');
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
    public function add(Request $request, Manager $fractal)
    {
        $user = $request->user();
        # get the currently authenticated user
        if (!($user->is_professional || $user->is_vendor)) {
            throw new RecordNotFoundException(
                'The directory function has not been enabled on this account.'
            );
        }
        $this->validate($request, [
            'title' => 'required|max:300',
            'type' => 'nullable|string|in:professional,vendor',
            'cost_type' => 'required|string|in:free,paid',
            'cost_frequency' => 'nullable|in:hour,day,week,month,standard',
            'cost_currency' => 'nullable|string|size:3',
            'cost_amount' => 'required|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'required|string',
            'extra_categories' => 'nullable|array',
            'extra_categories.*' => 'required|string',
        ]);
        # validate the request

        /*if (empty($request->cost_amount) && $request->cost_type  !== "free")  {
            //throw ValidationException::withMessages(['cost_amount' => 'You must enter a value for PAID services']);
            throw new ValidationException ('You must enter a value for PAID services');
            //$error = \Illuminate\Validation\ValidationException::withMessages([
            //   ['cost_amount' => 'You must enter a value for PAID services'],
            //]);
            //throw $error;
        }*/


        $categories = [];
        # selected category ids
        if ($request->has('categories')) {
            $categories = ProfessionalCategory::whereIn('uuid', $request->categories)->pluck('id')->all();
            # get the categories
        }
        if ($request->has('extra_categories') && count($request->extra_categories) > 0) {
            # there are extra category names packed in the request to be created
            foreach ($request->extra_categories as $name) {
                $c = ProfessionalCategory::create(['name' => $name]);
                $categories[] = $c->id;
            }
        }
        if (empty($categories)) {
            # could not find any of the specified categories
            throw new RecordNotFoundException('Could not find any of the selected service categories.');
        }
        $service = null;
        # the service model
        $service = $user->professionalServices()->create([
            'title' => $request->title,
            'type' => $request->input('type', 'professional'),
            'cost_type' => $request->cost_type,
            'cost_frequency' => $request->input('cost_frequency', 'standard'),
            'cost_currency' => $request->input('cost_currency', 'NGN'),
            'cost_amount' => $request->cost_amount
        ]);
        # create the model
        $service->categories()->sync($categories);
        # link up the service -> categories
        $resource = new Item($service, new ProfessionalServiceTransformer(), $service->type . '_service');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $user = $request->user();
        # get the currently authenticated user
        if (!($user->is_professional || $user->is_vendor)) {
            throw new RecordNotFoundException(
                'The professional directory function has not been enabled on this account.'
            );
        }
        $service = $user->professionalServices()->with('categories')->where('uuid', $id)->firstOrFail();
        # get the service
        if (!(clone $service)->delete()) {
            throw new DeletingFailedException('Failed while deleting the service.');
        }
        $resource = new Item($service, new ProfessionalServiceTransformer(), $service->type . '_service');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $service = ProfessionalService::with('categories', 'user')->where('uuid', $id)->first();
        if (empty($service)) {
            $service = VendorService::with('categories', 'user')->where('uuid', $id)->firstOrFail();
        }
        # get the service
        $resource = new Item($service, new ProfessionalServiceTransformer(), $service->type . '_service');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $user = $request->user();
        # get the currently authenticated user
        if (!($user->is_professional || $user->is_vendor)) {
            throw new RecordNotFoundException(
                'The professional directory function has not been enabled on this account.'
            );
        }
        $service = $user->professionalServices()->where('uuid', $id)->first();
        if (empty($service)) {
            $service = $user->vendorServices()->where('uuid', $id)->firstOrFail();
        }
        # get the service
        $this->validate($request, [
            'title' => 'nullable|max:300',
            'cost_type' => 'nullable|string|in:free,paid',
            'cost_frequency' => 'nullable|in:hour,day,week,month,standard',
            'cost_currency' => 'nullable|string|size:3',
            'cost_amount' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*' => 'required|string',
            'extra_categories' => 'nullable|array',
            'extra_categories.*' => 'required|string',
        ]);
        # validate the request
        $categories = [];
        # selected category ids
        if ($request->has('categories')) {
            $categories = ProfessionalCategory::whereIn('uuid', $request->categories)->pluck('id')->all();
            # get the categories
            if (empty($categories)) {
                throw new RecordNotFoundException('Could not find any of the selected service categories.');
            }
        }
        if ($request->has('extra_categories') && count($request->extra_categories) > 0) {
            # there are extra category names packed in the request to be created
            foreach ($request->extra_categories as $name) {
                $c = ProfessionalCategory::create(['name' => $name]);
                $categories[] = $c->id;
            }
        }
        if (!empty($categories)) {
            # we're fixing the categories
            $service->categories()->sync($categories);
            # link up the service -> categories
        }
        $this->updateModelAttributes($service, $request);
        # update the details on the model
        $service->saveOrFail();
        # save the changes
        $resource = new Item($service, new ProfessionalServiceTransformer(), $service->type . '_service');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}