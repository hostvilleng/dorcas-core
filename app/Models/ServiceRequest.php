<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceRequest extends Model
{
    protected $casts = [
        'is_read' => 'boolean'
    ];
    
    protected $fillable = [
        'uuid',
        'company_id',
        'service_id',
        'message',
        'attachment_url',
        'is_read',
        'status'
    ];
    
    protected $table = 'professional_service_requests';
    
    /**
     * @return null|string
     */
    public function getAttachmentFullUrlAttribute()
    {
        if (empty($this->attributes['attachment_url'])) {
            return null;
        }
        return Storage::disk(config('filesystems.default'))->url($this->attributes['attachment_url']);
    }
    
    /**
     * @return bool
     */
    public function getIsAcceptedAttribute(): bool
    {
        return $this->attributes['status'] === 'accepted';
    }
    
    /**
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->attributes['status'] === 'pending';
    }
    
    /**
     * @return bool
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->attributes['status'] === 'rejected';
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
    public function service()
    {
        return $this->belongsTo(ProfessionalService::class, 'service_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendorService()
    {
        return $this->belongsTo(VendorService::class, 'service_id');
    }
}