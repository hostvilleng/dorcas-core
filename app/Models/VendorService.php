<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;

class VendorService extends ProfessionalService
{
    protected $table = 'professional_services';
    
    /**
     * @inheritdoc
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 'vendor');
        });
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProfessionalCategory::class, 'professional_category_services', 'professional_service_id');
    }
}