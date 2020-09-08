<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\AccountingReportConfiguration;
use League\Fractal\TransformerAbstract;

class AccountingReportConfigurationTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['u', 'company'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['accounts'];
    
    /**
     * @param AccountingReportConfiguration $reportConfiguration
     *
     * @return array
     */
    public function transform(AccountingReportConfiguration $reportConfiguration)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $reportConfiguration->uuid,
            'report_name' => $reportConfiguration->report_name,
            'display_name' => title_case(str_replace('_', ' ', $reportConfiguration->report_name)),
            'configuration' => $reportConfiguration->configuration,
            'updated_at' => !empty($reportConfiguration->updated_at) ? $reportConfiguration->updated_at->toIso8601String() : null,
            'created_at' => $reportConfiguration->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param AccountingReportConfiguration $reportConfiguration
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeAccounts(AccountingReportConfiguration $reportConfiguration)
    {
        $accounts = $reportConfiguration->accounts;
        # get the accounts
        if (empty($accounts)) {
            return null;
        }
        return $this->collection($accounts, new AccountingAccountTransformer(), 'account');
    }
    
    /**
     * @param AccountingReportConfiguration $reportConfiguration
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(AccountingReportConfiguration $reportConfiguration)
    {
        return $this->item($reportConfiguration->company, new CompanyTransformer(), 'company');
    }
}