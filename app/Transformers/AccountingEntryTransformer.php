<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\AccountingEntry;
use League\Fractal\TransformerAbstract;

class AccountingEntryTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['account'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['account'];
    
    /**
     * @param AccountingEntry $accountingEntry
     *
     * @return array
     */
    public function transform(AccountingEntry $accountingEntry)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $accountingEntry->uuid,
            'currency' => $accountingEntry->currency,
            'amount' => [
                'raw' => $accountingEntry->amount,
                'formatted' => number_format($accountingEntry->amount, 2)
            ],
            'memo' => $accountingEntry->memo,
            'source_type' => $accountingEntry->source_type,
            'source_info' => $accountingEntry->source_info,
            'entry_type' => $accountingEntry->entry_type,
            'is_credit' => $accountingEntry->is_credit,
            'is_debit' => $accountingEntry->is_debit,
            'updated_at' => !empty($accountingEntry->updated_at) ? $accountingEntry->updated_at->toIso8601String() : null,
            'created_at' => $accountingEntry->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param AccountingEntry $accountingEntry
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeAccount(AccountingEntry $accountingEntry)
    {
        return $this->item($accountingEntry->accountingAccount, new AccountingAccountTransformer(), 'account');
    }
}