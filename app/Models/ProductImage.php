<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'uuid',
        'product_id',
        'url'
    ];

    /**
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::disk(config('filesystems.default'))->url($this->attributes['url']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}