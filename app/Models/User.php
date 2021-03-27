<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Auth\Authorizable as AuthorizableTrait;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements Authenticatable, Authorizable
{
    use HasApiTokens, AuthenticatableTrait, AuthorizableTrait, SoftDeletes, Searchable, Notifiable, HasRoles;

    protected $casts = [
        'extra_configurations' => 'array',
        'is_partner' => 'boolean',
        'is_professional' => 'boolean',
        'is_vendor' => 'boolean',
        'is_verified' => 'boolean',
        'is_employee' => 'boolean'
    ];
    
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'uuid',
        'company_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'address',
        'remember_token',
        'gender',
        'photo_url',
        'is_verified',
        'is_partner',
        'is_professional',
        'is_vendor',
        'is_employee',
        'partner_id',
        'extra_configurations'
    ];

    protected $hidden = ['password', 'remember_token'];
    
    /**
     * Adds a 'name' attribute on the model
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }

    /**
     * Returns the photo URL for this model
     *
     * @return string
     */
    public function getPhotoAttribute(): string
    {
        $storage_gravatar_url = !empty($this->attributes['photo_url']) && substr_count($this->attributes['photo_url'],"gravatar.com") > 0 ? $this->attributes['photo_url'] : Storage::disk(config('filesystems.default'))->url($this->attributes['photo_url']);
        return !empty($this->attributes['photo_url']) ? $storage_gravatar_url : gravatar($this->attributes['email']);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adverts()
    {
        return $this->hasMany(Advert::class, 'poster_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'bankable');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function blogPosts()
    {
        return $this->morphMany(BlogPost::class, 'poster');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companyAccessGrants()
    {
        return $this->hasMany(UserAccessGrant::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function domains()
    {
        return $this->morphMany(Domain::class, 'domainable');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function invites()
    {
        return $this->morphMany(Invite::class, 'inviter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function professionalCredentials()
    {
        return $this->hasMany(ProfessionalCredential::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function professionalExperiences()
    {
        return $this->hasMany(ProfessionalExperience::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function professionalServices()
    {
        return $this->hasMany(ProfessionalService::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function professionalServiceRequests()
    {
        return $this->hasManyThrough(
            ServiceRequest::class,
            ProfessionalService::class,
            'user_id',
            'service_id'
        );
    }

    public function approvals(){
        return $this->belongsToMany(Approvals::class,'approval_authorizers','approval_id','user_id')->withTimestamps()
            ->withPivot('approval_scope');
    }

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vendorServices()
    {
        return $this->hasMany(VendorService::class);
    }
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $searchable = $this->toArray();
        $searchable['company'] = $this->company->toArray();
        $searchable['professional_credentials'] = $this->professionalCredentials->toArray();
        $searchable['professional_experiences'] = $this->professionalExperiences->toArray();
        return $searchable;
    }



}