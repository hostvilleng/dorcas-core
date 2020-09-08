<?php

namespace App\Http\Controllers\ECommerce\Blog;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Transformers\BlogMediaTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Media extends Controller
{
    
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
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = empty($company) || empty($company->id) ? $this->company() : $company;
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->blogMedia()->withCount('posts')->oldest('title')->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new BlogMediaTransformer(), 'media');
        # create the resource
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
            'title' => 'nullable|string',
            'type' => 'required|string|in:image,video',
            'file' => 'required_without:url|image',
            'url' => 'required_without:file|url',
        ]);
        # validate the request
        $data = ['title' => $request->input('title'), 'type' => $request->input('type', 'image')];
        if ($request->has('file')) {
            $path = $request->file('image')->store('company-' . $company->uuid.'/blog-media');
            # upload the image for storage
            $data['filename'] = $path;
        } elseif ($request->has('url')) {
            if ($data['type'] === 'image') {
                $rawImage = file_get_contents($request->input('url'));
                Storage::putFile('company-' . $company->uuid.'/blog-media', $rawImage, 'public');
            } else {
                $data['filename'] = $request->input('url');
            }
        }
        $media = $company->blogMedia()->create($data);
        # create the model
        $resource = new Item($media, new BlogMediaTransformer(), 'media');
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
        $media = $company->blogCategories()->withCount('posts')->where('uuid', $id)->firstOrFail();
        # try to get the media
        if (!(clone $media)->delete()) {
            throw new DeletingFailedException('Failed while deleting the media');
        }
        $resource = new Item($media, new BlogMediaTransformer(), 'media');
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
     */
    public function single(Request $request, Manager $fractal, string $id, Company $company = null)
    {
        $media = $company->blogMedia()->withCount('posts')->where('uuid', $id)->firstOrFail();
        # try to get the media
        $resource = new Item($media, new BlogMediaTransformer(), 'media');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}