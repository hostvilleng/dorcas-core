<?php


namespace App\Transformers;
use App\Dorcas\Common\APITransformerTrait;
use App\Models\PayrollTransactions;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PayrollTransactionTransformer extends TransformerAbstract
{

    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company','employee'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['company','employee'];

    /**
     * @param PayrollTransactions $payrollTransaction
     *
     * @return array
     */

    public function transform(PayrollTransactions $payrollTransaction){
        $resource =  [
            'embeds' => $this->getEmbeds(),
            'id' => $payrollTransaction->uuid,
            'remarks' => $payrollTransaction->remarks,
            'amount_type' => $payrollTransaction->amount_type,
            'status_type' => $payrollTransaction->status_type,
            'amount' => $payrollTransaction->amount,
            'updated_at' => !empty($payrollTransaction->updated_at) ? $payrollTransaction->updated_at->toIso8601String() : null,
            'created_at' => $payrollTransaction->created_at->toIso8601String(),
            'links' => [
                'self' => url('/payroll/transactions', [$payrollTransaction->uuid])
            ],

        ];
        if($payrollTransaction->end_time){
            $resource['end_time'] = $payrollTransaction->end_time;
        }

        return  $resource;
    }

    /**
     * @param PayrollTransactions $transactions
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(PayrollTransactions $transactions)
    {
        return $this->item($transactions->company, new CompanyTransformer(), 'company');
    }


//    public function includeRun(PayrollTransactions $transactions){
//        return $this->item($transactions->runs, new PayrollRunTransformer(),'payrollrun');
//    }

    public function includeEmployee(PayrollTransactions $transactions, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $transactions->employee()->take($limit)
            ->offset($offset)
            ->oldest('firstname')
            ->oldest('lastname')
            ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }

}