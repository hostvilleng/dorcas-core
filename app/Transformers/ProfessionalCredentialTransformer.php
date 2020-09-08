<?php
/**
 * Created by PhpStorm.
 * User: eokeke
 * Date: 5/11/18
 * Time: 2:02 PM
 */

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ProfessionalCredential;
use League\Fractal\TransformerAbstract;

class ProfessionalCredentialTransformer extends TransformerAbstract
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
     * @param ProfessionalCredential $credential
     * @return array
     */
    public function transform(ProfessionalCredential $credential)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $credential->uuid,
            'title' => $credential->title,
            'type' => $credential->type,
            'description' => $credential->description,
            'year' => $credential->year,
            'certification' => $credential->certification,
        ];
    }
    
    /**
     * @param ProfessionalCredential $credential
     * @return mixed
     */
    public function includeProduct(ProfessionalCredential $credential)
    {
        return $this->item($credential->user, new UserTransformer(), 'user');
    }
}