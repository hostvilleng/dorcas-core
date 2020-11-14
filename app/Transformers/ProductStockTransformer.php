<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProductStock;
use League\Fractal\TransformerAbstract;

class ProductStockTransformer extends TransformerAbstract
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
     * @param ProductStock $stock
     *
     * @return array
     */
    public function transform(ProductStock $stock)
    {
        return  [
            'embeds' => $this->getEmbeds(),
            'id' => $stock->id,
            'action' => $stock->action,
            'quantity' => $stock->quantity,
            'comment' => $stock->comment,
            'updated_at' => !empty($stock->updated_at) ? $stock->updated_at->toIso8601String() : null,
            'created_at' => $stock->created_at->toIso8601String()
        ];
    }

    /**
     * @param ProductStock $stock
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeProduct(ProductStock $stock)
    {
        return $this->item($stock->product, new ProductTransformer(), 'product');
    }
}