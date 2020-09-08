<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProfessionalCredential extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'type',
        'description',
        'year',
        'certification',
    ];
    
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