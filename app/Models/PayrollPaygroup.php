<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPaygroup extends Model

{
    protected $table = 'payroll_paygroup';

    protected  $fillable = [
        'uuid',
        'group_name',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(){
        return $this->belongsToMany(Employee::class)->withTimestamps();
    }

    public function allowances(){
        return $this->belongsToMany(PayrollAllowances::class,'allowance_payroll_paygroup')->withTimestamps();
    }

//    public function allowances(){
//        return$this->belongsToMany(PayrollAllowances::class,'payroll_paygroup_allowances','paygroup_id','allowance_id');
//    }
}