<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProfessionalCategory;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ProfessionalCategoryTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['children', 'parent', 'services'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['parent'];
    
    /**
     * @param ProfessionalCategory $category
     *
     * @return array
     */
    public function transform(ProfessionalCategory $category)
    {
        $count = [];
        foreach ($category->getAttributes() as $key => $value) {
            if (!ends_with($key, '_count')) {
                continue;
            }
            $count[substr($key, 0, -6)] = number_format($value);
        }
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $category->uuid,
            'name' => $category->name,
            'counts' => $count
        ];
    }
    
    /**
     * @param ProfessionalCategory $category
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeChildren(ProfessionalCategory $category)
    {
        if ($category->children->count() === 0) {
            return null;
        }
        $transformer = new ProfessionalCategoryTransformer();
        $transformer->setDefaultIncludes([]);
        return $this->collection($category->children, $transformer, 'professional_category');
    }
    
    /**
     * @param ProfessionalCategory $category
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeParent(ProfessionalCategory $category)
    {
        if (empty($category->parent_id)) {
            return null;
        }
        $transformer = new ProfessionalCategoryTransformer();
        $transformer->setDefaultIncludes([]);
        return $this->item($category->parent, $transformer, 'professional_category');
    }
    
    /**
     * @param ProfessionalCategory $category
     * @param ParamBag|null        $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeServices(ProfessionalCategory $category, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $services = $category->services()->take($limit)
                                            ->offset($offset)
                                            ->oldest('title')
                                            ->get();
        return $this->collection($services, new ProfessionalServiceTransformer(), 'professional_service');
    }
}