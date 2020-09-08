<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxAuthority extends Model
{
    protected  $fillable = [
        'uuid',
        'authority_name',
        'payment_mode',
        'default_payment_details',
        'payment_details',
        'isActive'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function elements(){
        return $this->hasMany(TaxElements::class);
    }



}