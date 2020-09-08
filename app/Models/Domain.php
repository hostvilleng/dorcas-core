<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Domain extends Model
{
    use Searchable;

    protected $casts = [
        'configuration_json' => 'array'
    ];

    protected $fillable = [
        'uuid',
        'domainable_type',
        'domainable_id',
        'domain',
        'hosting_box_id',
        'configuration_json',
        'updated_at',
        'created_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function domainable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuances()
    {
        return $this->hasMany(DomainIssuance::class);
    }
}