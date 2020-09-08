<?php


namespace App\Jobs\Finance;

use App\Dorcas\Support\TimePeriod;
use App\Jobs\Job;
use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\TaxRuns;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class TaxRun extends Job
{


    public function handle()
    {
        try{
            $builder = TaxRuns::with('taxElement')->where('isActive',1);
            if(empty($builder->first())){
                throw new RecordNotFoundException('No Active Tax Runs Found');
            }
            $runs = (clone $builder)->orderBy('id','desc');
            $runs->chunk(200, function ($task_runs) use(&$test_array) {
                $current_day = date('d');
                $current_year = date('Y-m-d 00:00:00');
                foreach ($task_runs as $run) {
                    if(!empty($run->taxElement->frequency_month) &&  (int) $current_day  !== (int) $run->taxElement->frequency_month) {
                        continue;
                    }
                    if(!empty($run->taxElement->frequency_year) && (string)  $current_year !== (string) $run->taxElement->frequency_year) {
                        continue;
                    }
                    $accountIds = collect(AccountingAccount::whereIn('uuid',$run->taxElement->target_account)->get()
                        ->toArray())->pluck('id');
                    $type_data = collect(json_decode($run->taxElement->type_data, true));
                    $taxable_income = 0;
                    switch ($type_data['element_type']){
                        case 'others':
                            break;
                        case 'Percentage':
                            $taxable_income = $type_data['value'];
                            break;
                        default:
                            return false;
                    }
                    if($run->taxElement->frequency === 'monthly'){
                        $from = Carbon::parse('this month')->firstOfMonth();
                        $to = Carbon::parse('this month')->lastOfMonth();
                    }
                    elseif ($run->taxElement->frequency === 'yearly' ){
                        $previous_first_of_month =  Carbon::parse('previous month ')->firstOfMonth();
                        $previous_last_of_month =  Carbon::parse('previous month + 11 months')->lastOfMonth();
                        $from = Carbon::parse('previous year '.$previous_first_of_month);
                        $to = Carbon::parse('previous year '.$previous_last_of_month);
                    }
                    $period =  new TimePeriod ($from, $to);
                    $final_transaction['run'] = $run->id;
                    $final_transaction['authority'] = $run->taxElement->tax_authority_id;
                    $final_transaction['taxable_income'] = $taxable_income ;
                    $final_transaction['total'] = (0.01 * $taxable_income) * (array_sum($this->getAccountAmounts($accountIds,$period)));
                    DB::transaction(function () use (&$final_transaction) {
                        DB::table('tax_run_authorities')->insert([
                            'run_id' => $final_transaction['run'],
                            'authority_id' => $final_transaction['authority'],
                            'amount' => $final_transaction['total'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        DB::table('tax_runs')->where('id',$final_transaction['run'])
                            ->update([
                               'status' => 'processed',
                            ]);
                    });

                }
            });
        }
        catch (\Exception $e){
            return $e->getMessage();
        }

    }

    private function getAccountAmounts($accountIds, TimePeriod $period): array
        {
            $betweenDatesB = [
                $period->from->format('Y-m-d H:i:s'),
                $period->to->format('Y-m-d H:i:s'),
            ];
            $sections = [];
            $builder = AccountingEntry::with('accountingAccount')->whereIn('account_id', $accountIds);
            $entriesB = (clone $builder)->whereBetween('created_at', $betweenDatesB);
            $entriesB->chunk(200, function ($entries) use (&$sections) {
                foreach ($entries as $entry) {
                    $accountKey = $entry->accountingAccount->uuid;
                    $sections[] += $entry->amount;
                }

                # add the amount to the total
            });

            return $sections;
        }


}