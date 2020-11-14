<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'contactable_type',
        'contactable_id',
        'type',
        'firstname',
        'lastname',
        'email',
        'phone',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'bankable');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function contactable()
    {
        return $this->morphTo();
    }
}