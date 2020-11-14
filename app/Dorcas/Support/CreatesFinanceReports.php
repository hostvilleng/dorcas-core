<?php

namespace App\Dorcas\Support;


use App\Models\AccountingEntry;
use App\Models\AccountingReportConfiguration;

trait CreatesFinanceReports
{   
    public function createBalanceSheetReport(AccountingReportConfiguration $configuration, TimePeriod $period)
    {
        $accounts = $configuration->accounts;
        # get the configured accounts
        if (empty($accounts) || $accounts->count() === 0) {
            throw new \UnexpectedValueException('There are no accounts configured for this report.');
        }

        //$comparePeriod = $period->getSamePeriodFromPastYear(1, $period->from, $period->to);

        $accountsByParent = [];
        foreach ($accounts as $account) {
            $parent = $account->parentAccount;
            if (empty($accountsByParent[$parent->uuid])) {
                $accountsByParent[$parent->uuid] = ['account' => $parent, 'sub_accounts' => []];
            }
            $accountsByParent[$parent->uuid]['sub_accounts'][] = $account;
            # add the account
        }
        //$keyA = $period->from->format('Y');
        $keyB = $period->to->format('Y');
        //$sections = [$keyA => [], $keyB => []];
        $sections = [$keyB => []];
        # our data sections - an array like: ['2015' => [], '2016' => []]
        /*$betweenDatesA = [
            $comparePeriod->from->format('Y-m-d H:i:s'),
            $comparePeriod->to->format('Y-m-d H:i:s'),
        ];*/
        # the dates from the preceding period
        $betweenDatesB = [
            $period->from->format('Y-m-d H:i:s'),
            $period->to->format('Y-m-d H:i:s'),
        ];
        # the dates from the current period
        foreach ($accountsByParent as $group) {
            //$sections[$keyA][$group['account']->uuid] = ['label' => $group['account']->display_name, 'accounts' => []];
            $sections[$keyB][$group['account']->uuid] = ['label' => $group['account']->display_name, 'accounts' => []];
            # set up the data container
            $accountIds = collect($group['sub_accounts'])->map(function ($account) {
                return $account->id;
            });
            $builder = AccountingEntry::with('accountingAccount')->whereIn('account_id', $accountIds);
            //$entriesA = (clone $builder)->whereBetween('created_at', $betweenDatesA);
            # get data from the two periods
            /*$entriesA->chunk(200, function ($entries) use (&$sections, $group, $keyA) {
                foreach ($entries as $entry) {
                    $accountKey = $entry->accountingAccount->uuid;
                    if (empty($sections[$keyA][$group['account']->uuid]['accounts'][$accountKey])) {
                        $sections[$keyA][$group['account']->uuid]['accounts'][$accountKey] = ['label' => $entry->accountingAccount->display_name, 'totals' => 0];
                    }
                }
                $sections[$keyA][$group['account']->uuid]['accounts'][$accountKey]['totals'] += $entry->amount;
                # add the amount to the total
            });*/
            $entriesB = (clone $builder)->whereBetween('created_at', $betweenDatesB);
            $entriesB->chunk(200, function ($entries) use (&$sections, $group, $keyB) {
                foreach ($entries as $entry) {
                    $accountKey = $entry->accountingAccount->uuid;
                    if (empty($sections[$keyB][$group['account']->uuid]['accounts'][$accountKey])) {
                        $sections[$keyB][$group['account']->uuid]['accounts'][$accountKey] = ['label' => $entry->accountingAccount->display_name, 'totals' => 0];
                    }
                }
                $sections[$keyB][$group['account']->uuid]['accounts'][$accountKey]['totals'] += $entry->amount;
                # add the amount to the total
            });
        }

        //what do we have at the moment
        $res = array(
            "reportYears" => array("to" => $keyB),
            "reportAccounts" => $accounts,
            "reportParents" => $accountsByParent,
            "reportSections" => $sections,
            "reportYear" => $keyB

        );
        //strategy - loop sections

        return $res;
    }


    public function createIncomeStatementReport(AccountingReportConfiguration $configuration, TimePeriod $period)
    {
        $accounts = $configuration->accounts;
        # get the configured accounts
        if (empty($accounts) || $accounts->count() === 0) {
            throw new \UnexpectedValueException('There are no accounts configured for this report.');
        }

        //$comparePeriod = $period->getSamePeriodFromPastYear(1, $period->from, $period->to);

        $accountsByParent = [];
        foreach ($accounts as $account) {
            $parent = $account->parentAccount;
            if (empty($accountsByParent[$parent->uuid])) {
                $accountsByParent[$parent->uuid] = ['account' => $parent, 'sub_accounts' => []];
            }
            $accountsByParent[$parent->uuid]['sub_accounts'][] = $account;
            # add the account
        }
        //$keyA = $period->from->format('Y');
        $keyB = $period->to->format('Y');
        //$sections = [$keyA => [], $keyB => []];
        $sections = [$keyB => []];
        # our data sections - an array like: ['2015' => [], '2016' => []]
        /*$betweenDatesA = [
            $comparePeriod->from->format('Y-m-d H:i:s'),
            $comparePeriod->to->format('Y-m-d H:i:s'),
        ];*/
        # the dates from the preceding period
        $betweenDatesB = [
            $period->from->format('Y-m-d H:i:s'),
            $period->to->format('Y-m-d H:i:s'),
        ];
        # the dates from the current period
        foreach ($accountsByParent as $group) {
            //$sections[$keyA][$group['account']->uuid] = ['label' => $group['account']->display_name, 'accounts' => []];
            $sections[$keyB][$group['account']->uuid] = ['label' => $group['account']->display_name, 'accounts' => []];
            # set up the data container
            $accountIds = collect($group['sub_accounts'])->map(function ($account) {
                return $account->id;
            });
            $builder = AccountingEntry::with('accountingAccount')->whereIn('account_id', $accountIds);
            //$entriesA = (clone $builder)->whereBetween('created_at', $betweenDatesA);
            # get data from the two periods
            /*$entriesA->chunk(200, function ($entries) use (&$sections, $group, $keyA) {
                foreach ($entries as $entry) {
                    $accountKey = $entry->accountingAccount->uuid;
                    if (empty($sections[$keyA][$group['account']->uuid]['accounts'][$accountKey])) {
                        $sections[$keyA][$group['account']->uuid]['accounts'][$accountKey] = ['label' => $entry->accountingAccount->display_name, 'totals' => 0];
                    }
                }
                $sections[$keyA][$group['account']->uuid]['accounts'][$accountKey]['totals'] += $entry->amount;
                # add the amount to the total
            });*/
            $entriesB = (clone $builder)->whereBetween('created_at', $betweenDatesB);
            $entriesB->chunk(200, function ($entries) use (&$sections, $group, $keyB) {
                foreach ($entries as $entry) {
                    $accountKey = $entry->accountingAccount->uuid;
                    if (empty($sections[$keyB][$group['account']->uuid]['accounts'][$accountKey])) {
                        $sections[$keyB][$group['account']->uuid]['accounts'][$accountKey] = ['label' => $entry->accountingAccount->display_name, 'totals' => 0];
                    }
                }
                $sections[$keyB][$group['account']->uuid]['accounts'][$accountKey]['totals'] += $entry->amount;
                # add the amount to the total
            });
        }

        //what do we have at the moment
        $res = array(
            "reportYears" => array("to" => $keyB),
            "reportAccounts" => $accounts,
            "reportParents" => $accountsByParent,
            "reportSections" => $sections,
            "reportYear" => $keyB

        );
        //strategy - loop sections

        return $res;
    }

    
}