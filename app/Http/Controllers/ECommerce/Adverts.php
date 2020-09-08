<?php

namespace App\Http\Controllers\ECommerce;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Transformers\AdvertTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Adverts extends Controller
{
    protected $updateFields = [
        'type' => 'type',
        'title' => 'title',
        'redirect_url' => 'redirect_url',
        'extra_data' => 'extra_data',
        'is_default' => 'is_default'
    ];
    
    /**
     * @param Request      $request
     * @param Manager      $fractal
     * @param Company|null $company
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal, Company $company = null)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $defaultsOnly = (int) $request->input('default_only', 0);
        # get only default ad content
        $types = $request->input('types');
        # get the types to search within
        if (!empty($types)) {
            $types = explode(',', $types);
        }
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->adverts()->with('poster')
                                        ->when($defaultsOnly === 1, function ($query) {
                                            return $query->where('is_default', 1);
                                        })
                                        ->when($types, function ($query) use ($types) {
                                            return $query->whereIn('type', $types);
                                        })
                                        ->when($search, function ($query) use ($search) {
                                            return $query->where(function ($query) use ($search) {
                                                $query->where('title', 'like', '%' . $search . '%')
                                                        ->orWhere('type', 'like', '%' . $search . '%');
                                            });
                                        })
                                        ->latest()
                                        ->paginate($limit);
        # get the products
        $resource = new Collection($paginator->getCollection(), new AdvertTransformer(), 'advert');
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
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'type' => 'required|string',
            'title' => 'nullable|string',
            'image' => 'required|image',
            'redirect_url' => 'nullable|url',
            'is_default' => 'nullable|numeric|in:0,1',
            'extra_data' => 'nullable|array'
        ]);
        # validate the request
        $type = strtolower($request->input('type'));
        $path = $request->file('image')->store('company-' . $company->uuid.'/adverts');
        # upload the image for storage
        $isDefault = (int) $request->input('is_default', 0);
        # check the default status
        if ($isDefault === 1) {
            # we turn the others off
            $company->adverts()->where('type', $type)->update(['is_default' => 0]);
        }
        $advert = $company->adverts()->create([
            'type' => $type,
            'title' => $request->input('title'),
            'poster_id' => $request->user()->id,
            'image_filename' => !empty($path) ? $path : null,
            'redirect_url' => $request->input('redirect_url'),
            'is_default' => $isDefault,
            'extra_data' => $request->input('extra_data', []),
        ]);
        # create the model
        $resource = new Item($advert, new AdvertTransformer(), 'advert');
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
        $company = $this->company($request);
        # retrieve the company
        $advert = $company->adverts()->where('uuid', $id)->firstOrFail();
        # try to get the advert
        if (!(clone $advert)->delete()) {
            throw new DeletingFailedException('Failed while deleting the advert');
        }
        $transformer = new AdvertTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($advert, $transformer, 'advert');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request      $request
     * @param Manager      $fractal
     * @param string       $id
     * @param Company|null $company
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function single(Request $request, Manager $fractal, string $id, Company $company = null)
    {
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $advert = $company->adverts()->with(['poster'])->where('uuid', $id)->firstOrFail();
        # try to get the post
        $resource = new Item($advert, new AdvertTransformer(), 'advert');
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
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'type' => 'nullable|string',
            'title' => 'nullable|string',
            'image' => 'nullable|image',
            'redirect_url' => 'nullable|url',
            'is_default' => 'nullable|numeric|in:0,1',
            'extra_data' => 'nullable|array'
        ]);
        # validate the request
        $advert = $company->adverts()->with(['poster'])->where('uuid', $id)->firstOrFail();
        # try to get the post
        if ($request->has('type')) {
            $type = strtolower($request->input('type'));
            $request->request->set('type', $type);
        }
        if ($request->has('image')) {
            if (!empty($advert->image_filename)) {
                Storage::disk(config('filesystems.default'))->delete($advert->image_filename);
                # delete the current image
            }
            $advert->image_filename = $request->file('image')->store('company-' . $company->uuid.'/adverts');
        }
        # upload the image for storage
        $isDefault = (int) $request->input('is_default', 0);
        # check the default status
        if ($isDefault === 1) {
            # we turn the others off
            $company->adverts()->where('type', $type)->whereNotIn('uuid', [$id])->update(['is_default' => 0]);
        }
        $this->updateModelAttributes($advert, $request);
        # update the attributes
        $advert->saveOrFail();
        # commit the changes
        $resource = new Item($advert, new AdvertTransformer(), 'advert');
        return response()->json($fractal->createData($resource)->toArray());
    }
}