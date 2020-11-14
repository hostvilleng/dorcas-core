<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Location;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class LocationTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['employees', 'state'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['state'];

    /**
     * @param Location $location
     *
     * @return array
     */
    public function transform(Location $location)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $location->uuid,
            'name' => $location->name,
            'address1' => $location->address1,
            'address2' => $location->address2,
            'city' => $location->city,
            'is_trashed' => $location->deleted_at !== null,
            'trashed_at' => !empty($location->deleted_at) ? $location->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($location->updated_at) ? $location->updated_at->toIso8601String() : null,
            'created_at' => $location->created_at->toIso8601String(),
            'links' => [
                'self' => url('/locations', [$location->uuid])
            ],
            'counts' => [
                'employees' => !empty($location->employees_count) ? $location->employees_count : $location->employees()->count()
            ]
        ];
    }

    /**
     * @param Location      $location
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeEmployees(Location $location, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $location->employees()->take($limit)
                                            ->offset($offset)
                                            ->oldest('firstname')
                                            ->oldest('lastname')
                                            ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }

    /**
     * @param Location $location
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeState(Location $location)
    {
        return $this->item($location->state, new StateTransformer(), 'state');
    }
}