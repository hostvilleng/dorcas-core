<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ProfessionalExperience extends Model
{
    use Searchable;
    
    protected $fillable = [
        'uuid',
        'user_id',
        'company',
        'designation',
        'from_year',
        'to_year',
    ];
    
    /**
     * @return bool
     */
    public function getIsCurrentAttribute(): bool
    {
        return empty($this->attributes['to_year']);
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
        return $searchable;
    }
}