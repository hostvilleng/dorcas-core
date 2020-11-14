<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollAuthorities extends Model
{
    protected  $fillable = [
        'uuid',
        'authority_name',
        'payment_mode',
        'payment_details',
        'default_payment_details',
        'isActive'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function allowances(){
        return $this->hasMany(PayrollAllowances::class,'payroll_authority_id');
    }



}