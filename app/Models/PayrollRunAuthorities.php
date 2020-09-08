<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRunAuthorities extends Model
{

    protected $table = 'payroll_run_authorities';

    public function runs() {
        return $this->belongsToMany(PayrollRun::class,'payroll_run_authorities','authority_id','run_id')->withPivot('authority_id','run_id')
            ->using(PayrollAuthorities::class);
    }




}