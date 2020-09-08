<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\AccountingEntry;
use App\Models\UnverifiedTransactions;
use League\Fractal\TransformerAbstract;

class UnverifiedTransactionsTransformer extends TransformerAbstract
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
    protected $defaultIncludes = [];

    /**
     * @param UnverifiedTransactions $transactions
     *
     * @return array
     */
    public function transform(UnverifiedTransactions $transactions)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $transactions->uuid,
            'currency' => $transactions->currency,
            'amount' => [
                'raw' => $transactions->amount,
                'formatted' => number_format($transactions->amount, 2)
            ],
            'remark' => $transactions->remark,
            'status' => $transactions->status,
            'entry_type' => $transactions->entry_type,
            'updated_at' => !empty($transactions->updated_at) ? $transactions->updated_at->toIso8601String() : null,
            'created_at' => $transactions->created_at->toIso8601String()
        ];
    }

    /**
     * @param UnverifiedTransactions $transactions
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeAccount(UnverifiedTransactions $transactions)
    {
        return $this->item($transactions->AccountingAccounts(), new AccountingAccountTransformer(), 'account');
    }
}