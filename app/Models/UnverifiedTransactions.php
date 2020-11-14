<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\AccountingAccount;

class UnverifiedTransactions extends Model
{
    protected $fillable = [
        'account_id',
        'amount',
        'entry_type',
        'status',
        'remarks',
    ];

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

     
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function AccountingAccounts()
    {
        return $this->belongsTo(AccountingAccount::class,'account_id');
    }
}
