<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Plan;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PlanTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['companies'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param Plan $plan
     *
     * @return array
     */
    public function transform(Plan $plan)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $plan->uuid,
            'name' => $plan->name,
            'price_monthly' => [
                'raw' => $plan->price_monthly,
                'formatted' => number_format($plan->price_monthly, 2)
            ],
            'price_yearly' => [
                'raw' => $plan->price_yearly,
                'formatted' => number_format($plan->price_yearly, 2)
            ],
            'updated_at' => !empty($plan->updated_at) ? $plan->updated_at->toIso8601String() : null,
            'created_at' => $plan->created_at->toIso8601String()
        ];
        return $resource;
    }

    /**
     * @param Plan          $plan
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompanies(Plan $plan, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $companies = $plan->companies()->take($limit)
                                        ->offset($offset)
                                        ->oldest('name')
                                        ->get();
        return $this->collection($companies, new CompanyTransformer(), 'company');
    }
}