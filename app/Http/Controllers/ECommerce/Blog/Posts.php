<?php

namespace App\Http\Controllers\ECommerce\Blog;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Company;
use App\Transformers\BlogPostTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Posts extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'slug' => 'slug',
        'media_id' => 'media_id',
        'title' => 'title',
        'summary' => 'summary',
        'content' => 'content',
        'is_published' => 'is_published',
        'publish_at' => 'publish_at',
        'featured_at' => 'featured_at',
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
        $featuredOnly = (int) $request->input('featured_only', 0);
        # get only featured content
        $categorySlug = $request->input('category_slug');
        # get the category slug to search within
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $category = null;
        if ($categorySlug) {
            $category = BlogCategory::where('slug', $categorySlug)->first();
        }
        if (empty($search) || !empty($categorySlug)) {
            # no search parameter
            $paginator = $company->blogPosts()->with(['media', 'categories'])
                                                ->when($featuredOnly === 1, function ($query) {
                                                    return $query->whereNotNull('featured_at');
                                                })
                                                ->when($category, function ($query) use ($category) {
                                                    return $query->whereIn('id', function ($query) use ($category) {
                                                        $query->select('blog_post_id')
                                                                ->from('blog_category_post')
                                                                ->where('blog_category_id', $category->id);
                                                    });
                                                })
                                                ->when($search, function ($query) use ($search) {
                                                    return $query->where(function ($query) use ($search) {
                                                        $query->where('title', 'like', '%' . $search . '%')
                                                                ->orWhere('summary', 'like', '%' . $search . '%');
                                                    });
                                                })
                                                ->latest()
                                                ->paginate($limit);
        } else {
            # searching for something
            $paginator = BlogPost::search($search)->where('company_id', $company->id)->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new BlogPostTransformer(), 'post');
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
            'media' => 'nullable|string',
            'image' => 'nullable|image',
            'type' => 'required_with:url|string|in:image,video',
            'url' => 'nullable|url',
            'categories' => 'nullable|array',
            'categories.*' => 'required_with:categories|string',
            'title' => 'required|max:80',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|in:0,1',
            'publish_at' => 'required_if:is_published,0|date_format:"d/m/Y H:i"',
            'is_featured' => 'nullable|in:0,1'
        ]);
        # validate the request
        $slug = $company->id . '-' . str_slug($request->title);
        # set the slug
        if (BlogPost::where('slug', $slug)->count() > 0) {
            $slug .= '-' . uniqid();
        }
        $media = null;
        if ($request->has('media')) {
            $media = $company->blogMedia()->where('uuid', $request->input('media'))->first();
        } elseif ($request->has('image')) {
            $path = $request->file('image')->store('company-' . $company->uuid.'/blog-media');
            # upload the image for storage
            $media = $company->blogMedia()->create([
                'title' => $request->input('title'),
                'type' => 'image',
                'filename' => $path,
            ]);
        } elseif ($request->has('url')) {
            $media = $company->blogMedia()->create([
                'title' => $request->input('title'),
                'type' => $request->input('type'),
                'filename' => $request->input('url'),
            ]);
        }
        $categories = null;
        if ($request->has('categories')) {
            $categoryNames = $request->input('categories', []);
            # get them
            $categories = $company->blogCategories()->whereIn('uuid', $categoryNames)->get();
            if (empty($categories)) {
                # these categories do not exist, then we need to create them
                $categoriesData = [];
                foreach ($categoryNames as $name) {
                    $slug = $company->id . '-' . str_slug($name);
                    # set the slug
                    $categoriesData[] = ['name' => $request->input('name'), 'slug' => $slug];
                    # add the data
                }
                $categories = $company->blogCategories()->createMany($categoriesData);
                # add them
            }
        }
        $publishAt = null;
        if ($request->has('publish_at')) {
            $publishAt = Carbon::createFromFormat('d/m/Y H:i', $request->input('publish_at'));
        }
        $featureAt = null;
        if ($request->has('is_featured') && intval($request->input('is_featured', 0)) === 1) {
            $featureAt = Carbon::now();
        }
        $user = $request->user();
        # the posting user
        $post = $company->blogPosts()->create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'media_id' => !empty($media) ? $media->id : null,
            'poster_type' => $user->getMorphClass(),
            'poster_id' => $user->id,
            'summary' => $request->input('summary'),
            'content' => $request->input('content'),
            'is_published' => $request->input('is_published', 1),
            'publish_at' => !empty($publishAt) ? $publishAt->format('Y-m-d H:i:s') : null,
            'featured_at' => !empty($featureAt) ? $featureAt->format('Y-m-d H:i:s') : null
        ]);
        # create the model
        if (!empty($categories) && $categories->count() > 0) {
            $post->categories()->sync($categories->pluck('id'));
        }
        $resource = new Item($post, new BlogPostTransformer(), 'post');
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
        $post = $company->blogPosts()->where('uuid', $id)->firstOrFail();
        # try to get the post
        if (!(clone $post)->delete()) {
            throw new DeletingFailedException('Failed while deleting the post');
        }
        $transformer = new BlogPostTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($post, $transformer, 'post');
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
        $post = $company->blogPosts()->with(['media', 'categories'])->where($column, $id)->firstOrFail();
        # try to get the post
        $resource = new Item($post, new BlogPostTransformer(), 'post');
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
            'media' => 'nullable|string',
            'image' => 'nullable|image',
            'type' => 'required_with:url|string|in:image,video',
            'url' => 'nullable|url',
            'categories' => 'nullable|array',
            'categories.*' => 'required_with:categories|string',
            'title' => 'nullable|max:80',
            'slug' => 'nullable|max:80',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|in:0,1',
            'publish_at' => 'nullable|date_format:"d/m/Y H:i"',
            'is_featured' => 'nullable|in:0,1',
            'retain_photo' => 'nullable|in:0,1',
        ]);
        # validate the request
        $slug = $request->input('slug');
        # the slug to update to
        if ($request->has('update_slug') && $request->has('name')) {
            # update the slug, with the provided name
            $slug = $company->id . '-' . str_slug($request->name);
        }
        if (!empty($slug) && BlogPost::where('slug', $slug)->count() > 0) {
            throw new \UnexpectedValueException(
                'The slug "' . $slug.'" is already in use for another blog post.'
            );
        }
        if (!empty($slug)) {
            $request->request->set('slug', $slug);
        }
        $media = null;
        if ($request->has('media')) {
            $media = $company->blogMedia()->where('uuid', $request->input('media'))->first();
            
        } elseif ($request->has('image')) {
            $path = $request->file('image')->store('company-' . $company->uuid.'/blog-media');
            # upload the image for storage
            $media = $company->blogMedia()->create([
                'title' => $request->input('title'),
                'type' => 'image',
                'filename' => $path,
            ]);
        } elseif ($request->has('url')) {
            $media = $company->blogMedia()->create([
                'title' => $request->input('title'),
                'type' => $request->input('type'),
                'filename' => $request->input('url'),
            ]);
        }
        $post = $company->blogPosts()->where('uuid', $id)->firstOrFail();
        # try to get the post
        if (!empty($media)) {
            $request->request->set('media_id', $media->id);
        }
        if (!$request->has('retain_photo') && empty($media)) {
            $post->media_id = null;
            $request->request->remove('media_id');
            $media = $post->media;
            if (!empty($media) && $media->posts()->count() === 1) {
                $media->delete();
            }
        }
        if ($request->has('publish_at')) {
            $publishAt = Carbon::createFromFormat('d/m/Y H:i', $request->input('publish_at'));
            $request->request->set('is_published', 0);
            $request->request->set('publish_at', $publishAt->format('Y-m-d H:i:s'));
        }
        if ($request->has('is_featured') && intval($request->input('is_featured', 0)) === 1) {
            $request->request->set('featured_at', Carbon::now()->format('Y-m-d H:i:s'));
        }
        if ($request->has('categories')) {
            $categories = BlogCategory::whereIn('uuid', $request->input('categories'))
                                        ->where('company_id', $company->id)
                                        ->pluck('id');
            $post->categories()->sync($categories->all());
        }
        $this->updateModelAttributes($post, $request);
        # update the attributes
        $post->saveOrFail();
        # commit the changes
        $resource = new Item($post, new BlogPostTransformer(), 'post');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}