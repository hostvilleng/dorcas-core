<?php

namespace App\Http\Controllers\Finance;


use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Transformers\AccountingAccountTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class Setup extends Controller
{
    /** @var array  */
    protected $accounts = [
        [
            'name' => 'current assets',
            'type' => 'debit',
            'account' => 'assets',
            'is_visible' => true,
            'children' => ['cash', 'bank', 'inventory', 'accounts receivable', 'other current assets']
        ],
        [
            'name' => 'non-current assets',
            'type' => 'debit',
            'account' => 'assets',
            'is_visible' => true,
            'account' => 'assets',
            'children' => ['equipment', 'other non-current assets']
        ],
        [
            'name' => 'expenses',
            'type' => 'debit',
            'account' => 'expenses',
            'is_visible' => true,
            'account' => 'expenses',
            'children' => ['salaries', 'rent', 'depreciation', 'other expenses']
        ],
        [
            'name' => 'cost of goods sold',
            'type' => 'debit',
            'account' => 'expenses',
            'is_visible' => true,
            'children' => ['raw materials', 'other cost of goods sold']
        ],
        [
            'name' => 'tax',
            'type' => 'debit',
            'account' => 'expenses',
            'is_visible' => true,
            'children' => ['VAT', 'income tax', 'other tax']
        ],
        [
            'name' => 'current liabilities',
            'type' => 'credit',
            'account' => 'liabilities',
            'is_visible' => true,
            'children' => ['accounts payable', 'accrued expenses', 'other current liabilities']
        ],
        [
            'name' => 'long-term liabilities',
            'type' => 'credit',
            'account' => 'liabilities',
            'is_visible' => true,
            'children' => ['long-term debt', 'otherlong-term liabilities']
        ],
        [
            'name' => 'owners equity',
            'type' => 'credit',
            'account' => 'equity',
            'is_visible' => true,
            'account' => 'equity',
            'children' => ['capital', 'retained earnings', 'other owners equity']
        ],
        [
            'name' => 'revenue',
            'type' => 'credit',
            'account' => 'revenue',
            'is_visible' => true,
            'account' => 'revenue',
            'children' => ['sales', 'income', 'others']
        ],
        [
            'name' => 'unconfirmed',
            'type' => 'debit',
            'account' => 'unconfirmed',
            'is_visible' => false
        ],
        [
            'name' => 'unconfirmed',
            'type' => 'credit',
            'account' => 'unconfirmed',
            'is_visible' => false
        ],
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function install(Request $request, Manager $fractal)
    {
        $company = $this->company($request);
        # get the currently authenticated company
        $installed = 0;
        # total number of added accounts
        foreach ($this->accounts as $account) {
            $baseAccount = AccountingAccount::firstOrNew([
                'company_id' => $company->id,
                'name' => $account['name'],
                'entry_type' => $account['type'],
                'account_type' => $account['account']
            ]);
            # get the base account
            if (empty($baseAccount->uuid)) {
                # no UUID set on the account -- create a new one
                $baseAccount->is_visible = $account['is_visible'];
                # set properties
                $baseAccount->saveOrFail();
                ++$installed;
            }
            if (empty($account['children'])) {
                continue;
            }
            foreach ($account['children'] as $child) {
                $subAccount = $baseAccount->subAccounts()->firstOrNew(['name' => $child, 'company_id' => $company->id]);
                # get the sub-account, if any
                if (!empty($subAccount->uuid)) {
                    continue;
                }
                $subAccount->entry_type = $account['type'];
                $subAccount->account_type = $account['account'];
                $subAccount->is_visible = $account['is_visible'];
                $subAccount->account_type = '';
                $subAccount->account_code = '';
                $subAccount->saveOrFail();
                ++$installed;
            }
        }
        $accounts = $company->accountingAccounts()->oldest('name')->get();
        # fetch all accounting accounts for the authenticated company
        $resource = new Collection($accounts, new AccountingAccountTransformer(), 'account');
        # create the resource
        $resource->setMetaValue('installed', $installed);
        # set the meta for the number of installed accounts
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}