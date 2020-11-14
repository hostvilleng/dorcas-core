<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Team extends Model
{
    use Searchable;

    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'description'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function leaveGroups(){
        return $this->hasOne(LeaveGroups::class,'group_id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['company'] = $this->company->toArray();
        return $array;
    }
}
