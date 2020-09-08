<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ApprovalAuthorizers extends Model
{
    protected $fillable = [
        'user_id',
        'approval_id',
        'approval_scope'
    ];





}