<?php

namespace App\Models;


use App\Dorcas\Enum\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    use SoftDeletes;
    
    protected $casts = [
        'extra_data' => 'array',
        'is_verified' => 'boolean'
    ];

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'logo_url',
        'extra_data',
        'is_verified'
    ];
    
    /**
     * @return null|string
     */
    public function getLogoAttribute(): ?string
    {
        if (empty($this->attributes['logo_url'])) {
            return null;
        }
        if (starts_with($this->attributes['logo_url'], 'http')) {
            return $this->attributes['logo_url'];
        }
        return Storage::disk(config('filesystems.default'))->url($this->attributes['logo_url']);
    }
    
    /**
     * @return Builder|null
     */
    public function administrators(): ?Builder
    {
        $role = Role::where('name', RoleName::PARTNER)->firstOrFail();
        # get the role
        if (empty($role)) {
            return null;
        }
        return User::withTrashed()
                        ->with([
                            'company' => function ($query) {
                                $query->withTrashed();
                            }
                        ])
                        ->where('partner_id', $this->attributes['id'])
                        ->whereIn('id', function ($query) use ($role) {
                            $query->select('model_id')
                                    ->from('model_has_roles')
                                    ->where('model_type', (new User)->getMorphClass())
                                    ->where('role_id', $role->id);
                        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function companies()
    {
        return $this->hasManyThrough(Company::class, User::class, 'partner_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function domainIssuances()
    {
        return $this->morphMany(DomainIssuance::class, 'domainable');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function invites()
    {
        return $this->morphMany(Invite::class, 'inviter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}