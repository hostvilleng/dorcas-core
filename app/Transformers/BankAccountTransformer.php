<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\BankAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use League\Fractal\TransformerAbstract;

class BankAccountTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['owner'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['owner'];
    
    /**
     * @param BankAccount $bankAccount
     *
     * @return array
     */
    public function transform(BankAccount $bankAccount)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $bankAccount->uuid,
            'account_number' => $bankAccount->account_number,
            'account_name' => $bankAccount->account_name,
            'json_data' => $bankAccount->json_data,
            'updated_at' => !empty($bankAccount->updated_at) ? $bankAccount->updated_at->toIso8601String() : null,
            'created_at' => $bankAccount->created_at->toIso8601String()
        ];
    }

    /**
     * @param BankAccount $bankAccount
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeOwner(BankAccount $bankAccount)
    {
        $owner = $bankAccount->bankable;
        if ($owner instanceof Authenticatable) {
            return $this->item($owner, new UserTransformer(), 'user');
        }
        return $this->item($owner, new CompanyTransformer(), 'company');
    }
}