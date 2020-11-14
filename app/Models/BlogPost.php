<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class BlogPost extends Model
{
    use Searchable;
    
    protected $casts = [
        'is_published' => 'boolean',
    ];
    
    protected $dates = ['publish_at', 'featured_at'];
    
    protected $fillable = [
        'uuid',
        'slug',
        'company_id',
        'media_id',
        'poster_type',
        'poster_id',
        'title',
        'summary',
        'content',
        'is_published',
        'publish_at',
        'featured_at',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function media()
    {
        return $this->belongsTo(BlogMedia::class, 'media_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function poster()
    {
        return $this->morphTo();
    }
}