<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Integration;
use League\Fractal\TransformerAbstract;

class IntegrationTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Integration $integration
     *
     * @return array
     */
    public function transform(Integration $integration)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $integration->uuid,
            'type' => $integration->type,
            'name' => $integration->name,
            'configuration' => $integration->configuration,
            'updated_at' => !empty($integration->updated_at) ? $integration->updated_at->toIso8601String() : null,
            'created_at' => $integration->created_at->toIso8601String()
        ];
        return $resource;
    }

    /**
     * @param Integration $integration
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Integration $integration)
    {
        return $this->item($integration->company, new CompanyTransformer(), 'company');
    }
}