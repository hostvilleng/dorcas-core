<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Invite extends Model
{
    use Notifiable;
    
    protected $casts = [
        'config_data' => 'array'
    ];
    
    protected $fillable = [
        'uuid',
        'inviter_type',
        'inviter_id',
        'firstname',
        'lastname',
        'email',
        'message',
        'config_data',
        'status'
    ];
    
    /**
     * @return bool
     */
    public function getAttributeIsAccepted(): bool
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
     * @return User|null
     */
    public function getInvitingUserAttribute(): ?User
    {
        $config = json_decode($this->attributes['config_data'] ?? '{}');
        if (empty($config->inviting_user_id)) {
            return null;
        }
        return User::find($config->inviting_user_id);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function inviter()
    {
        return $this->morphTo();
    }
}
