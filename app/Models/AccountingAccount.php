<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    protected $casts = [
        'is_visible' => 'boolean'
    ];
    
    protected $fillable = [
        'uuid',
        'company_id',
        'parent_account_id',
        'name',
        'display_name',
        'entry_type',
        'is_visible',
        'account_code',
        'account_type',
    ];
    
    /**
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return !empty($this->attributes['display_name']) ? $this->attributes['display_name'] : title_case($this->attributes['name']);
    }
    
    /**
     * @return bool
     */


    
    /**
     * @return bool
     */
    public function getIsDebitAttribute(): bool
    {
        return $this->attributes['entry_type'] === 'debit';
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subAccounts()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_account_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {
        return $this->hasMany(AccountingEntry::class, 'account_id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unverifiedTransactions()
    {
        return $this->hasMany(UnverifiedTransactions::class,'account_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function parentAccount()
    {
        if (empty($this->attributes['parent_account_id'])) {
            return null;
        }
        return $this->belongsTo(AccountingAccount::class, 'parent_account_id');
    }
}