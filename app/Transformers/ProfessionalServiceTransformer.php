<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProfessionalService;
use League\Fractal\TransformerAbstract;

class ProfessionalServiceTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['categories', 'user'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['categories', 'user'];
    
    /**
     * @param ProfessionalService $service
     *
     * @return array
     */
    public function transform(ProfessionalService $service)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $service->uuid,
            'title' => $service->title,
            'type' => $service->type,
            'cost_type' => $service->cost_type,
            'cost_frequency' => $service->cost_frequency,
            'cost_currency' => $service->cost_currency,
            'cost_amount' => [
                'raw' => $service->cost_amount,
                'formatted' => number_format($service->cost_amount, 2)
            ],
            'updated_at' => !empty($service->updated_at) ? $service->updated_at->toIso8601String() : null,
            'created_at' => $service->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param ProfessionalService $service
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCategories(ProfessionalService $service)
    {
        $transformer = new ProfessionalCategoryTransformer();
        $transformer->setDefaultIncludes([]);
        return $this->collection($service->categories, $transformer, 'professional_category');
    }
    
    /**
     * @param ProfessionalService $service
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(ProfessionalService $service)
    {
        return $this->item($service->user, new UserTransformer(), 'user');
    }
}