<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Deal;
use League\Fractal\TransformerAbstract;

class DealTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['customer', 'stages'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['stages'];
    
    /**
     * @param Deal $deal
     *
     * @return array
     */
    public function transform(Deal $deal)
    {
        $currentValue = 0;
        $stages = $deal->stages;
        # get the stages
        if (!empty($stages) && $stages->count() > 0) {
            # let's process the stages
            $currentValue = $stages->filter(function ($stage) {
                return $stage->entered_at !== null;
            })->sum('value_amount');
        }
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $deal->uuid,
            'name' => $deal->name,
            'value_currency' => $deal->value_currency,
            'value_amount' => [
                'raw' => $deal->value_amount,
                'formatted' => number_format($deal->value_amount, 2)
            ],
            'current_value' => [
                'raw' => $currentValue,
                'formatted' => number_format($currentValue, 2)
            ],
            'note' => $deal->note,
            'updated_at' => !empty($deal->updated_at) ? $deal->updated_at->toIso8601String() : null,
            'created_at' => $deal->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param Deal $deal
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Deal $deal)
    {
        return $this->item($deal->customer, new CustomerTransformer(), 'customer');
    }
    
    /**
     * @param Deal $deal
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeStages(Deal $deal)
    {
        return $this->collection($deal->stages, new DealStageTransformer(), 'deal_stage');
    }
}