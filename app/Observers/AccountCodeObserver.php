<?php

namespace App\Observers;


use App\Models\AccountingAccount;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;

class AccountCodeObserver
{

    protected $accountCodes = [
        [
            'name' => 'current assets',
            'type' => 'debit',
            'account' => 'assets',
            'code' => '11000'
        ],
        [
            'name' => 'non-current assets',
            'type' => 'debit',
            'account' => 'assets',
            'code' => '12000'

        ],
        [
            'name' => 'expenses',
            'type' => 'debit',
            'account' => 'expenses',
            'code' => '21000'

        ],
        [
            'name' => 'cost of goods sold',
            'type' => 'debit',
            'account' => 'expenses',
            'code' => '22000'

        ],
        [
            'name' => 'tax',
            'type' => 'debit',
            'account' => 'expenses',
            'code' => '23000'

        ],
        [
            'name' => 'current liabilities',
            'type' => 'credit',
            'account' => 'liabilities',
            'code' => '31000'

        ],
        [
            'name' => 'long-term liabilities',
            'type' => 'credit',
            'account' => 'liabilities',
            'code' => '32000'
        ],
        [
            'name' => 'owners equity',
            'type' => 'credit',
            'account' => 'equity',
            'code' => '40000'
        ],
        [
            'name' => 'revenue',
            'type' => 'credit',
            'account' => 'revenue',
            'code' => '50000'
        ],
        [
            'name' => 'unconfirmed',
            'type' => 'debit',
            'account' => 'unconfirmed',
            'code' => '61000'
        ],
        [
            'name' => 'unconfirmed',
            'type' => 'credit',
            'account' => 'unconfirmed',
            'code' => '62000'
        ],
    ];
    /**
     * @param Model $model
     * @throws \Exception
     */
    public function creating(Model $model)
    {
        if (in_array('account_code', $model->getFillable())) {
           $model->account_code = $this->setAccountCode($model->name,$model->entry_type,$model->parent_account_id);
        }
    }

    private function setAccountCode($name,$entry_type,$parent_account_id){
        //Level 1 Account Codes
        foreach ($this->accountCodes as $account) {
            if ($name === $account['name'] && $entry_type === $account['type']){
                return $account['code'];
            }
        }
        return $this->getAccountCode($parent_account_id);
    }

    private function getAccountCode($id){
        $account  = AccountingAccount::findorFail($id);;
        return $account->account_code + 1;
    }
}
