<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ApplicationInstall extends Model
{
    protected $casts = [
        'extra_json' => 'array'
    ];
    
    protected $fillable = [
        'uuid',
        'application_id',
        'company_id',
        'extra_json'
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}