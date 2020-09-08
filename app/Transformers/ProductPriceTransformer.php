<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProductPrice;
use League\Fractal\TransformerAbstract;

class ProductPriceTransformer extends TransformerAbstract
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
     * @param ProductPrice $price
     *
     * @return array
     */
    public function transform(ProductPrice $price)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $price->uuid,
            'currency' => $price->currency,
            'unit_price' => [
                'raw' => $price->unit_price,
                'formatted' => number_format($price->unit_price, 2)
            ]
        ];
    }

    /**
     * @param ProductPrice $price
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeProduct(ProductPrice $price)
    {
        return $this->item($price->product, new ProductTransformer(), 'product');
    }
}