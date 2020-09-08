<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class LeaveGroups extends Model
{
    use Searchable, SoftDeletes;
    protected $fillable = [
        'company_id',
        'group_id',
        'uuid',
        'group_type',
        'duration_days',
        'duration_term',
        'type_id'
    ];

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function requests(){
        return $this->hasMany(LeaveRequests::class);
    }

    public function types(){
        return $this->belongsToMany(LeaveTypes::class,'leave_group_type','leave_group_id','leave_type_id')->withTimestamps();
    }

    public function teams(){
        return $this->belongsTo(Team::class,'group_id');
    }

    public function departments(){
        return $this->hasOne(Department::class,'group_id');
    }




}