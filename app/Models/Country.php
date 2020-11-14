<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Country extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'uuid',
        'name',
        'iso_code',
        'dialing_code'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function locations()
    {
        return $this->hasManyThrough(
            Location::class,
            State::class,
            'country_id',
            'state_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();
        $states = $this->states;
        foreach ($states as $state) {
            $array['states'][]['state_name'] = $state->name;
        }
        return $array;
    }
}