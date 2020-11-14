<?php

namespace App\Models;


use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;

class UserAccessGrant extends Model
{
    protected $casts = [
        'extra_json' => 'array'
    ];
    
    protected $dates = ['status_updated_at'];
    
    protected $fillable = [
        'uuid',
        'user_id',
        'company_id',
        'access_token',
        'status',
        'extra_json',
        'status_updated_at',
    ];
    
    protected $table = 'user_access_grants';
    
    /**
     * @return null|string
     */
    public function getAccessUrlAttribute(): ?string
    {
        if ($this->attributes['status'] !== 'accepted') {
            return '';
        }
        $company = $this->company()->with(['domainIssuances'])->first();
        return url_from_company($company, 'home', ['token' => $this->attributes['access_token']]);
    }
    
    /**
     * @return bool
     */
    public function getIsAcceptedAttribute(): bool
    {
        return $this->attributes['status'] === 'accepted';
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}