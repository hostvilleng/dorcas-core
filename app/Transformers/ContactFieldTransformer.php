<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ContactField;
use League\Fractal\TransformerAbstract;

class ContactFieldTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param ContactField $field
     *
     * @return array
     */
    public function transform(ContactField $field)
    {
        $resource = ['embeds' => $this->getEmbeds(), 'id' => $field->uuid, 'name' => $field->name];
        if (!empty($field->pivot)) {
            $resource['value'] = $field->pivot->value ?? null;
        }
        return $resource;
    }
}