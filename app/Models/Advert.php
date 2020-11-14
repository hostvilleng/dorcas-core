<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Advert extends Model
{
    protected $casts = [
        'is_default' => 'boolean',
        'extra_data' => 'array',
    ];
    
    protected $fillable = [
        'uuid',
        'company_id',
        'poster_id',
        'title',
        'image_filename',
        'redirect_url',
        'extra_data',
        'is_default'
    ];
    
    /**
     * @return null|string
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->attributes['image_filename'])) {
            return null;
        }
        return Storage::disk(config('filesystems.default'))->url($this->attributes['image_filename']);
    }
    
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
    public function poster()
    {
        return $this->belongsTo(User::class, 'poster_id');
    }
}