<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Group;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class GroupTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'customers'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Group $group
     *
     * @return array
     */
    public function transform(Group $group)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $group->uuid,
            'name' => $group->name,
            'customers_count' => $group->customers()->count(),
            'description' => $group->description,
            'updated_at' => !empty($group->updated_at) ? $group->updated_at->toIso8601String() : null,
            'created_at' => $group->created_at->toIso8601String()
        ];
        return $resource;
    }

    /**
     * @param Group $group
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Group $group)
    {
        return $this->item($group->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param Group $group
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCustomers(Group $group, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $customers = $group->customers()->take($limit)
                                        ->offset($offset)
                                        ->oldest('firstname')
                                        ->oldest('lastname')
                                        ->get();
        return $this->collection($customers, new CustomerTransformer(), 'customer');
    }
}