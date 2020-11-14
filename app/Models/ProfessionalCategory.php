<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ProfessionalCategory extends Model
{
    use Searchable;
    
    protected $fillable = [
        'uuid',
        'parent_id',
        'name'
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(ProfessionalCategory::class, 'parent_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(ProfessionalCategory::class, 'parent_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(ProfessionalService::class, 'professional_category_services');
    }
    
    /**
     * @return array
     */
    public function toSearchableArray()
    {
        $searchable = $this->toArray();
        if (!empty($this->parent)) {
            $searchable['parent'] = $this->parent->toArray();
        }
        return $searchable;
    }
}