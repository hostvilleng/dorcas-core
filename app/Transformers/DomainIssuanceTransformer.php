<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Domain;
use App\Models\DomainIssuance;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DomainIssuanceTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['domain', 'owner'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['domain'];

    /**
     * @param DomainIssuance $issuance
     *
     * @return array
     */
    public function transform(DomainIssuance $issuance)
    {
        return  [
            'embeds' => $this->getEmbeds(),
            'id' => $issuance->uuid,
            'prefix' => $issuance->prefix,
            'updated_at' => !empty($issuance->updated_at) ? $issuance->updated_at->toIso8601String() : null,
            'created_at' => $issuance->created_at->toIso8601String()
        ];
    }

    /**
     * @param DomainIssuance $issuance
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeDomain(DomainIssuance $issuance)
    {
        $domain = !empty($issuance->domain_id) ? $issuance->domain : $this->getDefaultDomain($issuance);
        return $this->item($domain, new DomainTransformer(), 'domain');
    }

    /**
     * @param DomainIssuance $issuance
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeOwner(DomainIssuance $issuance)
    {
        $domainable = $issuance->domainable;
        # get the owning entity
        try {
            $reflection = new \ReflectionClass(get_class($domainable));
            $transformer = 'App\\Transformers\\' . $reflection->getShortName() . 'Transformer';
            return $this->item($domainable, new $transformer(), 'owner');

        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * Creates a default domain entry for those created on the dorcas.ng domain.
     *
     * @param DomainIssuance $issuance
     *
     * @return Domain
     */
    protected function getDefaultDomain(DomainIssuance $issuance): Domain
    {
        return new Domain([
            'uuid' => 1,
            'domainable_type' => $issuance->domainable_type,
            'domainable_id' => $issuance->domainable_id,
            //'domain' => app()->environment() === 'production' ? 'dorcas.io' : 'dorcas.local',
            'domain' => in_array(config('dorcas.edition','business'), ['community', 'enterprise']) ? env("DORCAS_PARENT_DOMAIN", 'dorcas.default') : env("DORCAS_BASE_DOMAIN", 'dorcas.default'),
            'configuration_json' => [],
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now()
        ]);
    }
}