<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $casts = [
        'is_paid' => 'boolean'
    ];

    protected $fillable = [
        'uuid',
        'name',
        'display_name',
        'description',
        'icon',
        'is_paid'
    ];

    /**
     * @return null|string
     */
    public function getIconUrlAttribute()
    {
        return $this->attributes['icon'] ? cdn($this->attributes['icon']): null;
    }

    /**
     * @return BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany(Company::class)->withPivot(['created_at']);
    }
}