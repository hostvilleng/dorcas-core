<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'uuid',
        'customer_id',
        'name',
        'value_currency',
        'value_amount',
        'note',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stages()
    {
        return $this->hasMany(DealStage::class);
    }
}