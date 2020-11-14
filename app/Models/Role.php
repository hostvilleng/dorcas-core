<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $casts = [
        'extra_json' => 'array'
    ];
    
    /**
     * Adds a display_name attribute on the Role model.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return title_case(str_replace('.', ' ', $this->attributes['name']));
    }
}
