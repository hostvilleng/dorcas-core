<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DealStage extends Model
{
    protected $dates = ['entered_at'];
    
    protected $fillable = [
        'uuid',
        'deal_id',
        'name',
        'value_amount',
        'note',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}