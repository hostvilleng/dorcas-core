<?php

namespace App\Http\Controllers\Invoicing\Product;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Transformers\ProductImageTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class ProductImages extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'url' => 'url'
    ];

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
        $this->validate($request, [
            'id' => 'required_without:ids|string',
            'ids' => 'required_without:id|array'
        ]);
        # validate the request
        $product = $company->products()->with(['images'])->where('uuid', $id)->firstOrFail();
        # try to get the product
        $uuids = $request->has('ids') ? $request->input('ids') : [$request->input('id')];
        # our array of uuids to delete
        $filtered = $product->images->whereNotIn('uuid', $uuids);
        # filter the list, and remove the requested ids
        $imageFiles = $product->images->whereIn('uuid', $uuids)->pluck('url')->all();
        # the files to be deleted
        if (!$product->images()->whereIn('uuid', $uuids)->delete()) {
            # failed while deleting the currencies
            throw new DeletingFailedException(
                'Failed while trying to remove the requested images from the product.'
            );
        }
        Storage::disk(config('filesystems.default'))->delete($imageFiles);
        # remove the image files
        $resource = new Collection($filtered, new ProductImageTransformer(), 'image');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $product = $company->products()->with(['images'])->where('uuid', $id)->firstOrFail();
        # try to get the product
        $resource = new Collection($product->images, new ProductImageTransformer(), 'image');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'image' => 'required|image',
        ]);
        # validate the request
        $product = $company->products()->where('uuid', $id)->firstOrFail();
        # try to get the product
        $path = $request->file('image')->store('store-' . $company->uuid.'/product-' . $product->uuid);
        # upload the image for storage
        $product->images()->create(['url' => $path]);
        # create the model
        $resource = new Collection($product->images, new ProductImageTransformer(), 'image');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}