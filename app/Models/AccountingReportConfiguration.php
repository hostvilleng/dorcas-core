<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AccountingReportConfiguration extends Model
{
    protected $casts = [
        'configuration' => 'array'
    ];
    
    protected $fillable = [
        'uuid',
        'company_id',
        'report_name',
        'configuration',
    ];
    
    /**
     * @return null|Collection
     */
    public function getAccountsAttribute()
    {
        if (empty($this->attributes['configuration'])) {
            return null;
        }
        $configuration = $this->configuration;
        if (empty($configuration['accounts'])) {
            return null;
        }
        return AccountingAccount::where('company_id', $this->attributes['company_id'])
                                ->whereIn('id', $configuration['accounts'])
                                ->orderBy('parent_account_id')
                                ->get();
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}