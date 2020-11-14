<?php


namespace App\Http\Controllers\Finance\Tax;


use App\Dorcas\Support\TimePeriod;
use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingReportConfiguration;
use App\Models\TaxElements;
use App\Models\TaxRuns;
use App\Transformers\TaxRunTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exceptions\RecordNotFoundException;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;


class
TaxRun extends Controller
{
    private $taxRun;
    private $taxElement;
    public function __construct(TaxRuns $taxRuns, TaxElements $taxElements)
    {
        $this->taxRun = $taxRuns;
        $this->taxElement = $taxElements;
    }

    protected $updateFields =
        [
            'run_name' => 'run_name',
            'isActive' => 'isActive'

        ];

    /**
     * @param Request $request
     * @param Manager $fractal
     * @return \Illuminate\Http\JsonResponse
     */

    public function create( Request $request, Manager $fractal){
        # get the currently authenticated company
        $company = $this->company();
        $this->validate($request,[
            'element'=>'required|string',
            'run_name'=>'required|string',
        ]);
        $run = $this->taxElement->where('uuid',$request->element)->firstOrFail();
        if (empty($run)) {
            throw new RecordNotFoundException('Could not find the specified element to create tax run');
        }
        $tax_run=  $run->taxRuns()->create([
            'run_name'=> $request->run_name,
        ]);
        $resource = new Item($tax_run, new TaxRunTransformer(),'taxRun');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }


    /**
     * @param Request     $request
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
        # append values for the paginator
        if (empty($id)) {
            throw new RecordNotFoundException('Could not find the specified element search for run  in.');

        } else {
            $run = $this->taxElement->where('uuid', $id)->firstOrFail();
            if (empty($run)) {
                throw new RecordNotFoundException('Could not find the specified element to search for tax runs  in.');
            }
            $ids[] = $run->id;

            # the ids we're interested in
            $builder = $this->taxRun->whereIn('tax_element_id', $ids);
        }

        $paginator = $builder->when($search, function ($query) use ($search) {
            return $query->where('run_name', 'like', '%'.$search.'%');
        })
            ->latest()
            ->paginate($limit);

        $resource = new Collection($paginator->getCollection(), new TaxRunTransformer());
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

    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $run = $this->taxRun->where('uuid',$id)->firstorFail();
        # get the account
        $resource = new Item($run, new TaxRunTransformer(), 'taxRun');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function update(Request $request, Manager $fractal, string $id){
        $this->validate($request, [
            'element'=>['required','string'],
            'run_name' => ['required','string'],
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $run = $this->taxRun->where('uuid',[$id])->firstOrFail();

        if ($request->has('element')) {
            $element = $this->taxElement->where('uuid', $request->element)->first();
            if (empty($element)) {
                throw new RecordNotFoundException('Could not find the specified element to add the run  in.');
            }
            $run->tax_element_id = $element->id;
        }

        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($run, $request);
        # update the attributes
        $run->saveOrFail();
        # save the changes
        $resource = new Item($run, new TaxRunTransformer(), 'taxRun');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }



    public function start(Request $request, Manager $fractal, string $id){
        $this->validate($request, [
            'element'=>['required','string'],
            'isActive' => ['required']
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $run = $this->taxRun->where('uuid',[$id])->firstOrFail();

        if ($request->has('element')) {
            $element = $this->taxElement->where('uuid', $request->element)->first();
            if (empty($element)) {
                throw new RecordNotFoundException('Could not find the specified element to start the  tax run  in.');
            }
            $run->tax_element_id = $element->id;
        }

        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($run, $request);
        # update the attributes
        $run->saveOrFail();
        # save the changes
        $resource = new Item($run, new TaxRunTransformer(), 'taxRun');
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
        $run = $this->taxRun->where('uuid',[$id])->firstOrFail();

        # get the entry
        if (!(clone $run)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified Tax Run Please try again.'
            );
        }
        $transformer = new TaxRunTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($run, $transformer, 'taxRun');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function getRunAuthorities(string $id){
        $company = $this->company();
        $run = $this->taxRun->where('uuid',$id)->firstorFail();
        $processed_authorities = DB::table('tax_run_authorities')
            ->join('tax_runs','tax_runs.id','tax_run_authorities.run_id')
            ->join('tax_authorities','tax_authorities.id','tax_run_authorities.authority_id')
            ->where('tax_runs.id',$run->id)
            ->where('tax_runs.status','processed')
            ->groupBy(['tax_run_authorities.id'])
            ->select(DB::raw('SUM(tax_run_authorities.amount) AS total_amount'),
                'tax_authorities.authority_name','tax_authorities.uuid as id','tax_authorities.default_payment_details as payment_details')
            ->get();
        return response()->json(($processed_authorities)->toArray(), 200);
    }


}