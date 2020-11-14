<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class LeaveRequests extends Model
{
    use Searchable, SoftDeletes;

    protected $fillable = [
      'uuid',
      'group_id',
      'employee_id',
      'count_available',
      'count_utilized',
      'count_remaining',
      'count_requesting',
      'data_start_date',
      'data_report_back',
      'data_contact_address',
      'data_contact_phone',
      'data_backup_staff',
      'data_remarks',
      'approval_id',
      'type_id'
    ];

    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function approvals(){
        return $this->belongsTo(Approvals::class);
    }

    public function groups(){
        return $this->belongsTo(LeaveGroups::class,'group_id');
    }

    public function leaveTypes(){
        return $this->belongsTo(LeaveTypes::class,'type_id');
    }

    public function employees(){
        return $this->belongsTo(Employee::class,'employee_id');
    }

}