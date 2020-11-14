<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\CustomerNote;
use League\Fractal\TransformerAbstract;

class CustomerNoteTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['customer'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param CustomerNote $note
     *
     * @return array
     */
    public function transform(CustomerNote $note)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $note->uuid,
            'message' => $note->message,
            'updated_at' => !empty($note->updated_at) ? $note->created_at->toIso8601String() : null,
            'created_at' => $note->created_at->toIso8601String()
        ];
        return $resource;
    }

    /**
     * @param CustomerNote $note
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCustomer(CustomerNote $note)
    {
        return $this->item($note->customer, new CustomerTransformer(), 'customer');
    }
}