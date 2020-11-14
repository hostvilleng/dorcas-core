<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\State;
use League\Fractal\TransformerAbstract;

class StateTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['country'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param State $state
     *
     * @return array
     */
    public function transform(State $state)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $state->uuid,
            'name' => $state->name,
            'iso_code' => $state->iso_code,
            'created_at' => $state->created_at->toIso8601String(),
            'links' => [
                'self' => url('/states', [$state->uuid])
            ]
        ];
    }

    /**
     * @param State $state
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCountry(State $state)
    {
        return $this->item($state->country, new CountryTransformer(), 'country');
    }
}