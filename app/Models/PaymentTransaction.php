<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $casts = [
        'is_successful' => 'boolean',
        'json_payload' => 'array',
    ];

    protected $fillable = [
        'uuid',
        'order_id',
        'customer_id',
        'amount',
        'currency',
        'reference',
        'response_code',
        'response_description',
        'json_payload',
        'is_successful',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}