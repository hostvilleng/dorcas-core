<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use League\Fractal\TransformerAbstract;
use ReflectionClass;

class ContactTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['bank_accounts', 'contactable'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['contactable'];
    
    /**
     * @param Contact $contact
     *
     * @return array
     */
    public function transform(Contact $contact)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $contact->uuid,
            'firstname' => $contact->firstname,
            'lastname' => $contact->lastname,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ];
    }
    
    /**
     * @param Contact $contact
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeBankAccounts(Contact $contact)
    {
        if (!empty($contact->firstname)) {
            $bankAccounts = $contact->bankAccounts;
        } else {
            $contactable = $contact->contactable;
            if ($contactable instanceof User || $contactable instanceof Company) {
                $bankAccounts = $contactable->bankAccounts;
            } else {
                return null;
            }
        }
        return $this->collection($bankAccounts, new BankAccountTransformer(), 'bank_account');
    }
    
    /**
     * @param Contact $contact
     *
     * @return \League\Fractal\Resource\Item|null
     * @throws \ReflectionException
     */
    public function includeContactable(Contact $contact)
    {
        $reference = $contact->contactable;
        # get the model
        if (empty($reference)) {
            return null;
        }
        $reflection = new ReflectionClass($reference);
        $transformer = 'App\\Transformers\\' . $reflection->getShortName() . 'Transformer';
        # we set the transformer name
        if (!class_exists($transformer)) {
            return null;
        }
        $transformerInstance = new $transformer;
        $transformerInstance->setDefaultIncludes(['professional_services']);
        return $this->item($reference, $transformerInstance, snake_case($reflection->getShortName()));
    }
}