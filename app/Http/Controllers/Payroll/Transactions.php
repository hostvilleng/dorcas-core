<?php


namespace App\Http\Controllers\Payroll;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\PayrollTransactions;
use App\Transformers\PayrollTransactionTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Illuminate\Validation\Rule;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class Transactions extends  Controller
{
    private $transaction;
    const TYPE = ['deduction','addition'];
    const STATUS = ['one_time','repeat'];

    public function __construct(PayrollTransactions $transactions)
    {
        $this->transaction = $transactions;
    }

    protected $updateFields = [
        'remarks' => 'remarks',
        'status_type' => 'status_type',
        'amount' => 'amount',
        'amount_type' => 'amount_type',


    ];

    public function search(Request $request , Manager $fractal){
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->PayrollTransactions()->when($search, function ($query) use ($search) {
            return $query->where('remarks', 'like', '%'.$search.'%')
                ->orwhere('amount_type','like','%'.$search.'%');
        })
            ->oldest('remarks')
            ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new PayrollTransactionTransformer());
        # create the resource
        if (!empty($search)) {
            $pagingAppends['search'] = $search;
            # append the search term to the paginator
            $resource->setMetaValue('search', $search);
            # set the meta value if necessary
        }
        $paginator->appends($pagingAppends);
        # add the append terms
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function create( Request $request, Manager $fractal){
        $company = $this->company();
        $this->validate($request,[
            'employee'=>'required|string',
            'remarks'=>'required|string',
            'amount'=>'required|string',
            'amount_type' => ['required',Rule::in(self::TYPE),],
            'status_type' => ['required',Rule::in(self::STATUS),],
            'end_time' => 'nullable|date_format:Y-m-d'
        ]);
        $data = [
            'remarks'=> $request->remarks,
            'amount' => $request->amount,
            'amount_type'=> $request->amount_type,
            'status_type'=> $request->status_type
        ];
        if($request->has('end_time')){
            $data['end_time'] = $request->end_time;
        }

        $employee = $company->employees()->where('uuid', $request->employee)->firstOrFail();

        if (empty($employee)) {
            throw new RecordNotFoundException('Could not find the specified employee to add the transaction in.');
        }
        $data['company_id'] = $employee->company_id;
        $transaction = $employee->PayrollTransaction()->create($data);

        $resource = new Item($transaction, new PayrollTransactionTransformer());
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }



    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $transaction = $this->transaction->where('uuid',$id)->firstorFail();
        # get the account
        $resource = new Item($transaction, new PayrollTransactionTransformer(), 'PayrollTransaction');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request,[
            'employee'=>'required|string',
            'remarks'=>'required|string',
            'amount'=>'required|string',
            'amount_type' => ['required',Rule::in(self::TYPE),],
            'status_type' => ['required',Rule::in(self::STATUS),],
            'end_time' => 'nullable|date_format:Y-m-d'
        ]);
        # validate the request
        $transaction  = $company->PayrollTransactions()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $employee = $company->employees()->where('uuid', $request->employee)->firstOrFail();

        $transaction->employee_id = $employee->id;
        $this->updateModelAttributes($transaction, $request);
        # update the attributes
        $transaction->saveOrFail();
        # save the changes
        $resource = new Item($transaction, new PayrollTransactionTransformer(), 'PayrollTransaction');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $transaction = $company->PayrollTransactions()->where('uuid', $id)->firstOrFail();
        # try to get the group
        if (!(clone $transaction)->delete()) {
            throw new DeletingFailedException('Failed while deleting the Payroll Transaction');
        }
        $resource = new Item($transaction, new PayrollTransactionTransformer(), 'Payroll Transaction');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}