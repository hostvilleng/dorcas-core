<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProductImage;
use League\Fractal\TransformerAbstract;

class ProductImageTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['product'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param ProductImage $image
     *
     * @return array
     */
    public function transform(ProductImage $image)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $image->uuid,
            'url' => $image->image_url,
            'updated_at' => !empty($image->updated_at) ? $image->updated_at->toIso8601String() : null,
            'created_at' => $image->created_at->toIso8601String()
        ];
    }

    /**
     * @param ProductImage $image
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeProduct(ProductImage $image)
    {
        return $this->item($image->product, new ProductTransformer(), 'product');
    }
}