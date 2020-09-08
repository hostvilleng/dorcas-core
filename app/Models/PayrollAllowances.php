<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollAllowances extends Model
{

    protected  $fillable = [
        'uuid',
        'allowance_name',
        'allowance_type',
        'model',
        'company_id',
        'isActive',
        'model_data'


    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }
    public function authorities(){
        return $this->belongsTo(PayrollAuthorities::class,'payroll_authority_id');
    }

    public function PayrollGroup(){
        return $this->belongsToMany(PayrollPaygroup::class,'allowance_payroll_paygroup')->withTimestamps();
    }

}