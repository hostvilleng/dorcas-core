<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccountingEntry extends Model
{
    protected $fillable = [
        'uuid',
        'account_id',
        'entry_type',
        'currency',
        'amount',
        'memo',
        'source_type',
        'source_info',
        'updated_at',
        'created_at'
    ];
    
    /**
     * @return bool
     */
    public function getIsCreditAttribute(): bool
    {
        return $this->attributes['entry_type'] === 'credit';
    }
    
    /**
     * @return bool
     */
    public function getIsDebitAttribute(): bool
    {
        return $this->attributes['entry_type'] === 'debit';
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }
}