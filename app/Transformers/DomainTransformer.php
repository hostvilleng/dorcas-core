<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Domain;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class DomainTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['issuances', 'owner'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['owner'];

    /**
     * @param Domain $domain
     *
     * @return array
     */
    public function transform(Domain $domain)
    {
        return  [
            'embeds' => $this->getEmbeds(),
            'id' => $domain->uuid,
            'domain' => $domain->domain,
            'hosting_box_id' => $domain->hosting_box_id,
            'configuration' => $domain->configuration_json,
            'updated_at' => !empty($domain->updated_at) ? $domain->updated_at->toIso8601String() : null,
            'created_at' => $domain->created_at->toIso8601String()
        ];
    }

    /**
     * @param Domain        $domain
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeIssuances(Domain $domain, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $issuances = $domain->issuances()->take($limit)->offset($offset)->oldest('prefix')->get();
        return $this->collection($issuances, new DomainIssuanceTransformer(), 'issuance');
    }

    /**
     * @param Domain $domain
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeOwner(Domain $domain)
    {
        $domainable = $domain->domainable;
        # get the owning entity
        try {
            $reflection = new \ReflectionClass(get_class($domainable));
            $transformer = 'App\\Transformers\\' . $reflection->getShortName() . 'Transformer';
            return $this->item($domainable, (new $transformer())->setDefaultIncludes([]), 'owner');

        } catch (\ReflectionException $e) {
            return null;
        }
    }
}