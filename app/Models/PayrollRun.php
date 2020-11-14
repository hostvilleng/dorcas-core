<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'status',
        'run',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(){
        return $this->hasMany(PayrollTransactions::class);
    }

    public function employees(){
        return $this->belongsToMany(Employee::class,'payroll_run_histories','run_id','employee_id')->withTimestamps();
    }

//





}