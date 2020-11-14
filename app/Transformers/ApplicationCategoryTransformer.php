<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApplicationCategory;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ApplicationCategoryTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'applications'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    
    /**
     * @param ApplicationCategory $category
     *
     * @return array
     */
    public function transform(ApplicationCategory $category)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $category->uuid,
            'slug' => $category->slug,
            'name' => $category->name,
            'applications_count' => $category->applications()->count(),
            'updated_at' => !empty($category->updated_at) ? $category->updated_at->toIso8601String() : null,
            'created_at' => $category->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param ApplicationCategory $category
     * @param ParamBag|null       $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includePosts(ApplicationCategory $category, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $applications = $category->applications()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($applications, new ApplicationTransformer(), 'application');
    }
}