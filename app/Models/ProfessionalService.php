<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ProfessionalService extends Model
{
    use Searchable;
    
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'type',
        'cost_type',
        'cost_frequency',
        'cost_currency',
        'cost_amount',
    ];
    
    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'professional');
        });
    }
    
    /**
     * @return bool
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->attributes['cost_type'] === 'free';
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProfessionalCategory::class, 'professional_category_services');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * @return array
     */
    public function toSearchableArray()
    {
        $searchable = $this->toArray();
        $searchable['user'] = $this->user->toArray();
        $searchable['categories'] = $this->categories->toArray();
        return $searchable;
    }
}