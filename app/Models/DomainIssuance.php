<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Domain;

class DomainIssuance extends Model
{
    protected $fillable = [
        'uuid',
        'domain_id',
        'domainable_type',
        'domainable_id',
        'prefix'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function domainable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}