<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProfessionalExperience;
use League\Fractal\TransformerAbstract;

class ProfessionalExperienceTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['user'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    
    /**
     * @param ProfessionalExperience $experience
     * @return array
     */
    public function transform(ProfessionalExperience $experience)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $experience->uuid,
            'company' => $experience->company,
            'designation' => $experience->designation,
            'from_year' => $experience->from_year,
            'to_year' => $experience->to_year,
            'is_current' => $experience->is_current
        ];
    }
    
    /**
     * @param ProfessionalExperience $experience
     * @return \League\Fractal\Resource\Item
     */
    public function includeProduct(ProfessionalExperience $experience)
    {
        return $this->item($experience->user, new UserTransformer(), 'user');
    }
}