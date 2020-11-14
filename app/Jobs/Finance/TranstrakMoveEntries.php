<?php

namespace App\Jobs\Finance;


use App\Jobs\Job;
use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TranstrakMoveEntries extends Job
{
    /** @var array  */
    private $localCache = [];
    
    /**
     * Process these entries
     */
    public function handle()
    {
        DB::connection('mysql_transtrak')->transaction(function ($db) {
            # start a transaction on this
            $loops = 0;
            # we're limiting how many time it get's looped
            $builder = AccountingAccount::where('name', 'unconfirmed');
            # create the builder for getting accounts
            $deletes = [];
            # array of IDs to be deleted
            $db->table('transtrak_mail_export')->orderBy('id')->chunk(30, function ($exports) use ($builder, &$deletes, &$loops) {
                if ($loops >= 10) {
                    return false;
                }
                $now = Carbon::now()->format('Y-m-d H:i:s');
                # current MYSQL timestamp
                foreach ($exports as $export) {
                    $user = User::where('uuid', $export->account_user)->first();
                    if (empty($user)) {
                        # no user with this UUID
                        $deletes[] = $export->id;
                        continue;
                    }
                    $cacheKey = $user->company_id . '-' . strtolower($export->trans_type);
                    # cache key for storing the account entry
                    if (array_key_exists($cacheKey, $this->localCache)) {
                        # we already previously queried for this
                        $accountId = $this->localCache[$cacheKey];
                        
                    } else {
                        $account = (clone $builder)->where('company_id', $user->company_id)
                                                    ->where('entry_type', strtolower($export->trans_type))
                                                    ->first();
                        # query the database
                        if (empty($account)) {
                            # accounts have not been set up
                            $deletes[] = $export->id;
                            continue;
                        }
                        $this->localCache[$cacheKey] = $account->id;
                        # set it to the local cache for processing
                        $accountId = $account->id;
                    }
                    $transactionTime = empty($export->trans_time) ? '00:00:00' : $export->trans_time . ':00';
                    AccountingEntry::create([
                        'uuid' => Uuid::uuid1()->toString(),
                        'account_id' => $accountId,
                        'entry_type' => strtolower($export->trans_type),
                        'currency' => 'NGN',
                        'amount' => floatval(str_replace(',', '', $export->trans_amount)),
                        'memo' => $export->trans_remark,
                        'source_type' => 'transtrak',
                        'source_info' => $export->account_no .
                            (!empty($export->trans_location) ? ' - ' . $export->trans_location : ''),
                        'updated_at' => $now,
                        'created_at' => Carbon::createFromFormat('d-F-Y', $export->trans_date)->format('Y-m-d')
                            . ' ' . $transactionTime
                    ]);
                    # add the entry
                    $deletes[] = $export->id;
                }
                ++$loops;
            });
            $db->table('transtrak_mail_export')->whereIn('id', $deletes)->delete();
            # delete the processed records
        });
        return;
    }
}