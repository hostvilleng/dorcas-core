<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class LeaveTypes extends Model
{
    use Searchable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'approval_id',
        'title'
    ];

    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function groups(){
        return $this->belongsToMany(LeaveGroups::class,'leave_group_type','leave_type_id','leave_group_id')->withTimestamps();
    }

    public function approvals(){
        return $this->belongsTo(Approvals::class,'approval_id');
    }

    public function leaveRequests(){
        return $this->hasMany(LeaveRequests::class,'type_id');
    }



}