<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxElements extends Model
{
    protected $fillable = ['uuid','element_name','element_type','type_data','frequency','frequency_year','frequency_month','target_account'];

    protected $casts = [
        'target_account' => 'array',
    ];

    public function taxAuthority(){
        return $this->belongsTo(TaxAuthority::class,'tax_authority_id');
    }

    public function taxRuns(){
        return $this->hasMany(TaxRuns::class,'tax_element_id');
    }
}