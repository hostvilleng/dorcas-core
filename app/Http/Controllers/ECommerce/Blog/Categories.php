<?php

namespace App\Http\Controllers\ECommerce\Blog;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Company;
use App\Transformers\BlogCategoryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Categories extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'name' => 'name',
        'slug' => 'slug'
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
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = $company->blogCategories()->withCount('posts')->oldest('name')->paginate($limit);
        } else {
            # searching for something
            $paginator = BlogCategory::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new BlogCategoryTransformer(), 'category');
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
            'parent' => 'nullable|string',
            'name' => 'required|max:80',
        ]);
        # validate the request
        $slug = $company->id . '-' . str_slug($request->name);
        # set the slug
        if (BlogCategory::where('slug', $slug)->count() > 0) {
            $slug .= '-' . uniqid();
        }
        $parent = null;
        if ($request->has('parent')) {
            $parent = $company->blogCategories()->where('uuid', $request->input('parent'))->first();
        }
        $category = $company->blogCategories()->create([
            'name' => $request->input('name'),
            'slug' => $slug,
            'parent_id' => !empty($parent) ? $parent->id : null
        ]);
        # create the model
        $resource = new Item($category, new BlogCategoryTransformer(), 'category');
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
        $category = $company->blogCategories()->withCount('posts')->where('uuid', $id)->firstOrFail();
        # try to get the category
        if (!(clone $category)->delete()) {
            throw new DeletingFailedException('Failed while deleting the category');
        }
        $resource = new Item($category, new BlogCategoryTransformer(), 'category');
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
        $allowedColumns = ['uuid', 'slug'];
        # the columns we can select by
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $column = strtolower($request->has('select_using', 'uuid'));
        $column = !in_array($column, $allowedColumns) ? $allowedColumns[0] : $column;
        # fix a wrong select column, when required
        $category = $company->blogCategories()->withCount('posts')->where($column, $id)->firstOrFail();
        # try to get the category
        $resource = new Item($category, new BlogCategoryTransformer(), 'category');
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
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'name' => 'nullable|max:80',
            'slug' => 'nullable|max:80',
        ]);
        # validate the request
        $slug = $request->input('slug');
        # the slug to update to
        if ($request->has('update_slug') && $request->has('name')) {
            # update the slug, with the provided name
            $slug = $company->id . '-' . str_slug($request->name);
        }
        if (!empty($slug) && BlogCategory::where('slug', $slug)->count() > 0) {
            throw new \UnexpectedValueException(
                'The slug "' . $slug.'" is already in use for another blog category.'
            );
        }
        if (!empty($slug)) {
            $request->request->set('slug', $slug);
        }
        $category = $company->blogCategories()->withCount('posts')->where('uuid', $id)->firstOrFail();
        # try to get the category
        $this->updateModelAttributes($category, $request);
        # update the attributes
        $category->saveOrFail();
        # commit the changes
        $resource = new Item($category, new BlogCategoryTransformer(), 'category');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}