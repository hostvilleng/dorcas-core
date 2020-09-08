<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\DealStage;
use League\Fractal\TransformerAbstract;

class DealStageTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['deal'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    
    /**
     * @param DealStage $stage
     *
     * @return array
     */
    public function transform(DealStage $stage)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $stage->uuid,
            'name' => $stage->name,
            'value_amount' => [
                'raw' => $stage->value_amount,
                'formatted' => number_format($stage->value_amount, 2)
            ],
            'note' => $stage->note,
            'updated_at' => !empty($stage->updated_at) ? $stage->updated_at->toIso8601String() : null,
            'created_at' => $stage->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param DealStage $stage
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeDeal(DealStage $stage)
    {
        return $this->item($stage->deal, new DealTransformer(), 'deal');
    }
}