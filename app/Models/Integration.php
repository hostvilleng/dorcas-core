<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $casts = [
        'configuration' => 'array'
    ];

    protected $fillable = [
        'uuid',
        'company_id',
        'type',
        'name',
        'configuration',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}