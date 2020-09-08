<?php

namespace App\Jobs\People;

use App\Jobs\Job;
use App\Models\PayrollRun;
use App\Models\PayrollRunHistories;
use App\Models\PayrollTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeoplePayrollRun extends Job
{
    private $payrollRun;

    private $employee_invoice = array();
    /**
     * ChargeForPlan constructor.
     *
     * @param PayrollRun $payrollRun
     */
    public function __construct(PayrollRun $payrollRun)
    {
        $this->payrollRun = $payrollRun;
    }


//    private static $employee_invoice = array();

    public function handle()
    {
        $builder = $this->payrollRun;
        $final_output = [] ;
        $authorities = [];
        try {
            $builder->employees()->chunk(200, function ($employees) use(&$final_output, $builder, &$authorities) {
                $paygroup_ids = [];
                foreach ($employees as $employee) {
                    $base_salary = (int)$employee->salary_amount;
                    $this->employee_invoice['base_salary'] = $base_salary;
                    $paygroup_ids = [];
                    $calculated_transaction = 0;
                    if (!($employee->PayrollGroup)->isEmpty()) {
                        foreach ($employee->PayrollGroup as $paygroup) {
                            $paygroup_ids[] = $paygroup->id;

                            //This gets the sum of all transactions of all paygroup allowances per employee
                            $calculated_transaction += $this->processPaygroupAllowances($paygroup,$base_salary);

                            //This gets all sum of all  transaction of all allowances that has authority  per paygroup per employee
                            $authorities[] =  $this->processAuthorityAllowances($employee,$paygroup,$base_salary);
                            $this->processAuthorities($builder, $this->processAuthorityAllowances($employee,$paygroup,$base_salary));


                        }
                        $base_salary +=  $calculated_transaction;
                    }
                    if (!($employee->PayrollTransaction)->isEmpty()) {
                        $base_salary = $this->processEmployeeTransactions($employee,$base_salary);
                    }
                    $paygroup_ids = json_encode($paygroup_ids);
                    $this->processEmployees($builder,$paygroup_ids,$employee,$base_salary);
                    $final_output[] = [$employee->id,$base_salary, $paygroup_ids];
                   $this->employee_invoice= array();
                }

            });
            $this->payrollRun->status = 'processed';

            //This updates the pivot table for all run histories to processed
            DB::table('payroll_run_histories')
                ->where('run_id',$this->payrollRun->id)
                ->update([
                    'status' => 'processed',
                ]);
            if (!$this->payrollRun->save()) {
                throw new \Exception(
                    'Failed to save, the payroll run to processed'
                );
            }
        }

        catch (\UnexpectedValueException $e) {
            Log::info($e->getMessage(), $this->payrollRun->toArray());
        }
        return;
    }

    private function processAuthorities($run,$authorities_array){
        foreach ($authorities_array as $authorities){
            DB::table('payroll_run_authorities')
                ->insert([
                    'run_id' => $run->id,
                    'allowance_id' => $authorities['allowance_id'],
                    'authority_id' => $authorities['authority_id'],
                    'employee_id' => $authorities['employee_id'],
                    'amount' => $authorities['amount'],
                    'created_at' =>  \Carbon\Carbon::now(), # new \Datetime()
                    'updated_at' => \Carbon\Carbon::now(),
                ]);
        }


    }

    private function processEmployees($run,$paygroups,$employee,$amount){
        DB::table('payroll_run_employees')
            ->insert([
                'run_id' => $run->id,
                'paygroup_ids' => $paygroups,
                'employee_id' => $employee->id,
                'invoice_data' =>  json_encode($this->employee_invoice),
                'amount' => $amount,
                'created_at' =>  \Carbon\Carbon::now(), # new \Datetime()
                'updated_at' => \Carbon\Carbon::now(),
            ]);
    }

    private function processEmployeeTransactions($employee, $base_salary){
        $current_day = date('Y-m-d');
        foreach ($employee->PayrollTransaction as $transaction) {
            if($transaction->status_type === 'one_time' && $transaction->isPaid === 0 ){
                switch ($transaction->amount_type) {
                    case 'deduction':
                        $base_salary -= (int)$transaction->amount;
                        $this->employee_invoice['Transactions']['deductions'][$transaction->remarks] = $transaction->amount;
                        $transaction = PayrollTransactions::find($transaction->id);
                        $transaction->isPaid = 1;
                        $transaction->save();
                        break;
                    case 'addition':
                        $base_salary += (int)$transaction->amount;
                        $this->employee_invoice['Transactions']['additions'][$transaction->remarks] = $transaction->amount;
                        $transaction = PayrollTransactions::find($transaction->id);
                        $transaction->isPaid = 1;
                        $transaction->save();
                        break;
                    default:
                        return false;

                }
            }
            elseif ($transaction->status_type === 'repeat'){
                if($current_day <= $transaction->end_time){
                    switch ($transaction->amount_type) {
                        case 'deduction':
                            $base_salary -= (int)$transaction->amount;
                            $this->employee_invoice['Transactions']['deductions'][$transaction->remarks] = $transaction->amount;

                            break;
                        case 'addition':
                            $base_salary += (int)$transaction->amount;
                            $this->employee_invoice['Transactions']['additions'][$transaction->remarks] = $transaction->amount;
                            break;
                        default:
                            return false;

                    }
                }
            }

        }

        return $base_salary;
    }

    private function processPaygroupAllowances($paygroup, $base_salary)  {
        $amount = 0 ;
        if (!($paygroup->allowances)->isEmpty()) {
            foreach ($paygroup->allowances as $allowance) {
                $model_data =  collect(json_decode($allowance->model_data, true));
                switch ($allowance->model) {
                    case 'percent_of_base':
                        $employee_percent = (0.01 * $model_data['base_ratio'] );
                        $employer_percent = (0.01 * $model_data['employer_base_ratio'] );
                        if($allowance->allowance_type === 'benefit'){
                            $value  = ( $employee_percent * $base_salary ) + ($employer_percent * $base_salary);
                            $amount += $value;
                            $this->employee_invoice['Allowances']['benefit'][$allowance->allowance_name] = abs($amount);
                        }
                        else{
                            $value  = ( $employee_percent * $base_salary ) ;
                            $amount -= $value;
                            $this->employee_invoice['Allowances']['deduction'][$allowance->allowance_name] = abs($amount);

                        }
                        break;
                    case 'fixed':
                        if($allowance->allowance_type === 'benefit'){
                            $amount +=   $model_data['fixed_value'] ;
                            $this->employee_invoice['Allowances']['benefit'][$allowance->allowance_name] = abs($amount);

                        }
                        else{
                            $amount -= $model_data['fixed_value'];
                            $this->employee_invoice['Allowances']['deduction'][$allowance->allowance_name] = abs($amount);

                        }
                        break;
                    case 'computational':
                        if($allowance->allowance_type === 'benefit'){
                            $amount += $this->processPaygroupAllowancePayE($model_data,$base_salary);
                            $this->employee_invoice['Allowances']['benefit'][$allowance->allowance_name] = abs($amount);
                        }
                        else{
                            $amount -= $this->processPaygroupAllowancePayE($model_data,$base_salary);
                            $this->employee_invoice['Allowances']['deduction'][$allowance->allowance_name] = abs($amount);
                        }


                        break;
                    default:
                        throw new \Exception('Unexpected value');
                }

            }
        }
        return $amount;
    }

    private function processAuthorityAllowances($employee,$paygroup, $base_salary) : array  {
        $amount = 0 ;
        $authorities = [];
        if (!($paygroup->allowances)->isEmpty()) {
            foreach ($paygroup->allowances as $allowance) {
                $model_data = collect(json_decode($allowance->model_data, true));
                if ($allowance->authorities) {
                    switch ($allowance->model) {
                        case 'percent_of_base':
                            $employee_percent = (0.01 * $model_data['base_ratio']);
                            $employer_percent = (0.01 * $model_data['employer_base_ratio']);
                            if ($allowance->allowance_type === 'benefit') {
                                $value = ($employee_percent * $base_salary) + ($employer_percent * $base_salary);
                                $amount += $value;
                            } else {
                                $value = ($employee_percent * $base_salary);
                                $amount -= $value;
                            }
                            break;
                        case 'fixed':

                            if ($allowance->allowance_type === 'benefit') {
                                $amount += $model_data['fixed_value'];
                            } else {
                                $amount -= $model_data['fixed_value'];
                            }
                            break;
                        case 'computational':
                            if($allowance->allowance_type === 'benefit'){
                                $amount += $this->processPaygroupAllowancePayE($model_data,$base_salary);
                                $this->employee_invoice['Allowances']['benefit'][$allowance->allowance_name] = abs($amount);
                            }
                            else{
                                $amount -= $this->processPaygroupAllowancePayE($model_data,$base_salary);
                                $this->employee_invoice['Allowances']['deduction'][$allowance->allowance_name] = abs($amount);
                            }

                        default:
                            throw new \Exception('Unexpected value');
                    }
                    $authorities[] = ['allowance_id'=>$allowance->id,'amount'=>abs($amount),'authority_id'=>$allowance->payroll_authority_id,'employee_id'=>$employee->id];
                    $amount = 0;

                }
            }
        }
        return $authorities;
    }

    private function processPaygroupAllowancePayE($model_data, $base_salary)
    {
        $band = json_decode($model_data, true);
        $band_count = count($band);
        $salary_remaining = $base_salary * 12;
        $current_tax = 0;
        //check if the  band has values in it
        $i= 0;
        if($band_count <= 0 ){
            throw new \Exception('Invalid Band');
        }
        //check if the first band is not taxable
        if ($band[$i]["rate"] ===  0 && $salary_remaining <= $band[$i]['range']) {
            $current_tax = 0;
            return $current_tax;
        }
        if($band[$i]["rate"] ===  0 && $salary_remaining > $band[$i]['range']) {
            $i = 1;
        }
        //gets the first tax
        $current_tax = ($band[$i]['rate'] * 0.01) * $band[$i]['range'];
        //get the tax range
        $salary_check = $band[$i]['range'];
        $salary_remaining -= $salary_check;
        do{
            $i++;
            if ($band[$i]['range'] ===  0 ){
                $current_tax += ($band[$i]['rate'] * 0.01) * ($salary_remaining - $band[$i]['range']);
                break;
            }
            $current_tax += ($band[$i]['rate'] * 0.01) * $band[$i]['range'];
            $salary_check = $band[$i]['range'];
            $salary_remaining -= $salary_check;
        }while($salary_remaining > 0 );

        return  number_format((float)$current_tax/12, 2, '.', '');

    }

}