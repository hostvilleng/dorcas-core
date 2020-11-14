<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Client;

class Application extends Model
{
    protected $casts = [
        'extra_json' => 'array',
        'is_published' => 'boolean',
        'is_free' => 'boolean',
    ];
    
    protected $dates = ['published_at'];
    
    protected $fillable = [
        'uuid',
        'oauth_client_id',
        'user_id',
        'name',
        'type',
        'description',
        'homepage_url',
        'icon_filename',
        'banner_filename',
        'billing_type',
        'billing_period',
        'billing_currency',
        'billing_price',
        'is_published',
        'is_free',
        'extra_json',
        'published_at',
    ];
    
    /**
     * @return null|string
     */
    public function getIconAttribute(): ?string
    {
        if (empty($this->attributes['icon_filename'])) {
            return null;
        }
        return Storage::disk(config('filesystems.default'))->url($this->attributes['icon_filename']);
    }
    
    /**
     * @return null|string
     */
    public function getBannerAttribute(): ?string
    {
        if (empty($this->attributes['banner_filename'])) {
            return null;
        }
        return Storage::disk(config('filesystems.default'))->url($this->attributes['banner_filename']);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ApplicationCategory::class, 'application_category');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oauthClient()
    {
        return $this->belongsTo(Client::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installs()
    {
        return $this->hasMany(ApplicationInstall::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}