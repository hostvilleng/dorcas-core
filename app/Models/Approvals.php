<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
class Approvals extends Model
{
    use Searchable, SoftDeletes;
    protected $fillable = [
        'uuid',
        'company_id',
        'title',
        'scope_type',
        'active',
        'frequency_type',
        'scope_data',
    ];

    public function requests()
    {
        return $this->hasMany(ApprovalRequests::class,'approval_id');
    }

    public function authorizers(){
        return $this->belongsToMany(User::class,'approval_authorizers','approval_id','user_id')->withTimestamps()
            ->withPivot('approval_scope');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leaveRequest(){
        return $this->hasMany(LeaveRequests::class,'approval_id');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['company'] = $this->company->toArray();
        return $array;
    }
}
