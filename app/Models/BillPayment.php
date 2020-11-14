<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class BillPayment extends Model
{
    use Searchable;

    protected $casts = [
        'json_data' => 'array',
        'is_successful' => 'boolean'
    ];

    protected $fillable = [
        'company_id',
        'plan_id',
        'reference',
        'processor',
        'currency',
        'amount',
        'json_data',
        'is_successful'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}