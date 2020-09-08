<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\AccountingAccount;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class AccountingAccountTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'company',
        'entries',
        'sub_accounts',
        'parent_account',
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['parent_account'];
    
    /**
     * @param AccountingAccount $accountingAccount
     *
     * @return array
     */
    public function transform(AccountingAccount $accountingAccount)
    {
        $data = [
            'embeds' => $this->getEmbeds(),
            'id' => $accountingAccount->uuid,
            'name' => $accountingAccount->name,
            'display_name' => $accountingAccount->display_name,
            'entry_type' => $accountingAccount->entry_type,
            'is_credit' => $accountingAccount->is_credit,
            'is_debit' => $accountingAccount->is_debit,
            'account_code' => $accountingAccount->account_code,
            'is_visible' => $accountingAccount->is_visible,
            'updated_at' => !empty($accountingAccount->updated_at) ? $accountingAccount->updated_at->toIso8601String() : null,
            'created_at' => $accountingAccount->created_at->toIso8601String()
        ];
        $data['grandFather'] = (isAccountGrandFather($accountingAccount->id) ? true : null);
        $data['lastBorn'] =    (isNotLastBorn($accountingAccount->id) ? false : true );
        return $data;
    }
    
    /**
     * @param AccountingAccount $accountingAccount
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(AccountingAccount $accountingAccount)
    {
        return $this->item($accountingAccount->company, new CompanyTransformer(), 'company');
    }
    
    /**
     * @param AccountingAccount $accountingAccount
     * @param ParamBag|null     $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeEntries(AccountingAccount $accountingAccount, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $entries = $accountingAccount->entries()->take($limit)
                                                ->offset($offset)
                                                ->latest()
                                                ->get();
        return $this->collection($entries, new AccountingEntryTransformer(), 'account_entry');
    }

    public function includeTransactions(AccountingAccount $accountingAccount, ParamBag $params = null){
    	list($limit, $offset) = parse_fractal_params($params,0,10);
    	$transactions = $accountingAccount->unverifiedTransactions()->take($limit)
	                                                  ->offset($offset)
	                                                  ->latest()
	                                                  ->get();
    	return $this->collection($transactions, new UnverifiedTransactionsTransformer(),'account_transactions');
    }
    
    /**
     * @param AccountingAccount $accountingAccount
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSubAccounts(AccountingAccount $accountingAccount)
    {
        return $this->collection($accountingAccount->subAccounts, new AccountingAccountTransformer(), 'account');
    }
    
    /**
     * @param AccountingAccount $accountingAccount
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeParentAccount(AccountingAccount $accountingAccount)
    {
        if (empty($accountingAccount->parent_account_id)) {
            return null;
        }
        return $this->item($accountingAccount->parentAccount, new AccountingAccountTransformer(), 'account');
    }
}