<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $casts = [
        'json_data' => 'array'
    ];
    
    protected $fillable = [
        'uuid',
        'bankable_type',
        'bankable_id',
        'account_number',
        'account_name',
        'json_data'
    ];
    
    /**
     * Returns the processor for this bank account
     *
     * @return string
     */
    public function getProcessorAttribute(): string
    {
        $attributes = is_array($this->attributes['json_data']) ?
            $this->attributes['json_data'] : json_decode($this->attributes['json_data'], true);
        return $attributes['processor'] ?? '';
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function bankable()
    {
        return $this->morphTo();
    }
}