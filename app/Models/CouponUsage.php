<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'uuid',
        'coupon_id',
        'user_id'
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}