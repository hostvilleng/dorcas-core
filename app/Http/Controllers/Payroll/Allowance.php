<?php

namespace App\Http\Controllers\Payroll;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\PayrollAllowances;
use App\Transformers\PayrollAllowancesTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class Allowance extends Controller
{

    const TYPE = ['deduction','benefit'];
    const MODEL = ['percent_of_base','fixed','computational'];
    private $allowance;
    public function __construct(PayrollAllowances $allowance)
    {
        $this->allowance = $allowance;
    }


    protected $updateFields =
        [
            'allowance_name' => 'allowance_name',
            'model' => 'model',
            'allowance_type'=>'allowance_type',
            'model_data' => 'model_data'

        ];

    /*
     * @param Manager $fractal
     * @return \Illuminate\Http\JsonResponse
     */

    public function create( Request $request, Manager $fractal){
        # get the currently authenticated company
        $company = $this->company();
        $this->validate($request,[
            'authority'=>'nullable|string',
            'allowance_name'=> 'required|string',
            'allowance_type' => ['required',Rule::in(self::TYPE),],
            'model' => ['required',Rule::in(self::MODEL),],
            'model_data' => 'required|json',
        ]);
        $data = [
            'allowance_name'=> $request->allowance_name,
            'allowance_type' => $request->allowance_type,
            'model' => $request->model,
            'model_data' => $request->model_data,
        ];
        $allowance = null;
        if($request->has('authority')) {
            $authority = $company->PayrollAuthority()->where('uuid', $request->authority)->firstOrFail();
            if (empty($authority)) {
                throw new RecordNotFoundException('Could not find the specified authority to add the allowance in.');
            }
            $data['company_id'] = $authority->company_id;
            $allowance = $authority->allowances()->create($data);
        }
        else{
         $allowance = $company->PayrollAllowances()->create($data);
        }
        $resource = new Item($allowance, new PayrollAllowancesTransformer());
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    /**
     * @param Manager     $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $id = $request->input('id');
        # account we want to filter by
        $pagingAppends = ['limit' => $limit];
        $builder = $company->PayrollAllowances();
        # append values for the paginator

        $paginator = $builder->when($search, function ($query) use ($search) {
            return $query->where('allowance_name', 'like', '%'.$search.'%');
        })
            ->oldest('allowance_name')
            ->paginate($limit);


        $resource = new Collection($paginator->getCollection(), new PayrollAllowancesTransformer());

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

    /**
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */


    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $element = $this->allowance->where('uuid',$id)->firstorFail();
        # get the account
        $resource = new Item($element, new PayrollAllowancesTransformer(), 'payrollAllowance');
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
    public function update(Request $request, Manager $fractal, string $id){
        $this->validate($request, [
            'allowance_name' => ['required','string'],
            'allowance_type' => ['required',Rule::in(self::TYPE),],
            'model' => ['required',Rule::in(self::MODEL),],
            'model_data' => ['required'],
            'created_at' => 'nullable|date_format:Y-m-d'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $element = $company->PayrollAllowances()->where('uuid',[$id])->firstOrFail();

        if ($request->has('authority')) {
            $payrollAuthority = $company->PayrollAuthority()->where('uuid', $request->authority)->first();
            if (empty($payrollAuthority)) {
                throw new RecordNotFoundException('Could not find the specified authority  to add the allowance in.');
            }
            $element->payroll_authority_id = $payrollAuthority->id;
        }


        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($element, $request);
        # update the attributes
        $element->saveOrFail();
        # save the changes
        $resource = new Item($element, new PayrollAllowancesTransformer(), 'payrollAllowance');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }


    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * */

    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $allowance = $this->allowance->where('uuid',[$id])->firstOrFail();
        # get the entry
        if (!(clone $allowance)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified Payroll allowance Please try again.'
            );
        }
        $transformer = new PayrollAllowancesTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($allowance, $transformer, 'allowance');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }


}