<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BlogMedia extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'type',
        'title',
        'filename'
    ];
    
    /**
     * @return string
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk(config('filesystems.default'))->url($this->attributes['filename']);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'media_id');
    }
}