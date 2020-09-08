<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRuns extends Model
{
    protected $fillable = ['uuid','run_name','isActive','status'];

    public function taxElement(){
        return $this->belongsTo(TaxElements::class,'tax_element_id');
    }
}