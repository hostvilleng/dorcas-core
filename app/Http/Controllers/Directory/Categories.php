<?php

namespace App\Http\Controllers\Directory;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalCategory;
use App\Transformers\ProfessionalCategoryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class Categories extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'parent' => 'parent_id',
        'name' => 'name',
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
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = ProfessionalCategory::withCount('services')->oldest('name')->paginate($limit);
            # get the categories
        } else {
            # searching for something
            $paginator = ProfessionalCategory::search($search)->paginate($limit);
        }
        $resource = new Collection($paginator->getCollection(), new ProfessionalCategoryTransformer(), 'professional_category');
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
        # get the user
        if (!$user->is_professional) {
            throw new RecordNotFoundException(
                'The professional directory function has not been enabled on this account.'
            );
        }
        $this->validate($request, [
            'name' => 'required|max:80',
            'parent' => 'nullable|string'
        ]);
        # validate the request
        $parent = null;
        if ($request->has('parent')) {
            # get the parent category
            $parent = ProfessionalCategory::where('uuid', $request->parent)->first();
            # get it
            if (empty($parent)) {
                throw new RecordNotFoundException('Could not find the specified parent category.');
            }
        }
        $category = ProfessionalCategory::create([
            'name' => $request->name,
            'parent_id' => !empty($parent) ? $parent->id : null
        ]);
        # create the model
        $resource = new Item($category, new ProfessionalCategoryTransformer(), 'professional_category');
        return response()->json($fractal->createData($resource)->toArray(), 201);
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
        # get the user
        if (!$user->is_professional) {
            throw new RecordNotFoundException(
                'The professional directory function has not been enabled on this account.'
            );
        }
        $this->validate($request, [
            'name' => 'nullable|max:80',
            'parent' => 'nullable|string'
        ]);
        # validate the request
        if ($request->has('parent')) {
            # get the parent category
            $parent = ProfessionalCategory::where('uuid', $request->parent)->first();
            # get it
            if (!empty($parent)) {
                $request->request->set('parent', $parent->id);
            }
        }
        $category = ProfessionalCategory::where('uuid', $id)->firstOrFail();
        # find the model
        $this->updateModelAttributes($category, $request);
        # update the attributes
        $category->saveOrFail();
        # save the changes
        $resource = new Item($category, new ProfessionalCategoryTransformer(), 'professional_category');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}