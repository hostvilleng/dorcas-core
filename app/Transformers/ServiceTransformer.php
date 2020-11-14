<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Service;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['subscribers'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Service $service
     *
     * @return array
     */
    public function transform(Service $service)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $service->uuid,
            'name' => $service->name,
            'display_name' => $service->display_name,
            'description' => $service->description,
            'icon' => $service->icon_url,
            'is_paid' => $service->is_paid,
            'links' => [
                'self' => url('/services', [$service->uuid])
            ]
        ];
    }

    /**
     * @param Service       $service
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSubscribers(Service $service, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $companies = $service->subscribers()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($companies, new CompanyTransformer(), 'company');
    }
}