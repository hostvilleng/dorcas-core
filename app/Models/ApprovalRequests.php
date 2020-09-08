<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ApprovalRequests extends Model
{

    use Searchable, SoftDeletes;

    protected $fillable = [
      'uuid',
      'approval_id',
      'approval_comments',
      'approval_status',
      'model',
      'model_request_id',
      'model_data'

    ];
    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function approvals()
    {
        return $this->belongsTo(Approvals::class,'approval_id');
    }

}