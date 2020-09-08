<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Advert;
use League\Fractal\TransformerAbstract;

class AdvertTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'company',
        'poster'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['poster'];
    
    /**
     * @param Advert $advert
     *
     * @return array
     */
    public function transform(Advert $advert)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $advert->uuid,
            'type' => $advert->type,
            'title' => $advert->title,
            'image_url' => $advert->image_url,
            'redirect_url' => $advert->redirect_url,
            'extra_data' => $advert->extra_data,
            'is_default' => $advert->is_default,
            'updated_at' => !empty($advert->updated_at) ? $advert->updated_at->toIso8601String() : null,
            'created_at' => $advert->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param Advert $advert
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Advert $advert)
    {
        $transformer = (new CompanyTransformer())->setDefaultIncludes([])->setAvailableIncludes([]);
        return $this->item($advert->company, $transformer, 'company');
    }
    
    /**
     * @param Advert $advert
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includePoster(Advert $advert)
    {
        $transformer = (new UserTransformer())->setDefaultIncludes([])->setAvailableIncludes([]);
        return $this->item($advert->poster, $transformer, 'user');
    }
}