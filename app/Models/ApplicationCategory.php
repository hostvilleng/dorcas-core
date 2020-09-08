<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ApplicationCategory extends Model
{
    protected $fillable = [
        'uuid',
        'slug',
        'name',
        'description',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function applications()
    {
        return $this->belongsToMany(Application::class, 'application_category');
    }
}