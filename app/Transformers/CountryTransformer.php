<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Country;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class CountryTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['states'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Country $country
     *
     * @return array
     */
    public function transform(Country $country)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $country->uuid,
            'name' => $country->name,
            'iso_code' => $country->iso_code,
            'dialing_code' => $country->dialing_code,
            'is_trashed' => $country->deleted_at !== null,
            'trashed_at' => !empty($country->deleted_at) ? $country->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($country->updated_at) ? $country->updated_at->toIso8601String() : null,
            'created_at' => $country->created_at->toIso8601String(),
            'links' => [
                'self' => url('/countries', [$country->uuid])
            ]
        ];
    }

    /**
     * @param Country       $country
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeStates(Country $country, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 100);
        $states = $country->states()->take($limit)->offset($offset)->oldest('name')->get();
        return $this->collection($states, new StateTransformer(), 'state');
    }
}