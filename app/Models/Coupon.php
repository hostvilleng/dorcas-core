<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at', 'expires_at'];
    
    protected $casts = [
        'extra_data' => 'array'
    ];
    
    protected $fillable = [
        'uuid',
        'type',
        'plan_id',
        'user_id',
        'code',
        'currency',
        'amount',
        'max_usages',
        'description',
        'extra_data',
        'expires_at'
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}