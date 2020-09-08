<?php

namespace App\Http\Controllers\Companies;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\Plan;
use App\Transformers\BillPaymentTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class BillPayments extends Controller
{

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $company = \App\Models\Company::where('uuid', $id)->firstOrFail();
        # get the company
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = $company->billPayments()->latest()->paginate($limit);
        } else {
            # searching for something
            $paginator = BillPayment::search($search)
                                    ->where('company_id', $company->id)
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($limit);
        }
        # get the products
        $resource = new Collection($paginator->getCollection(), new BillPaymentTransformer(), 'bill_payment');
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
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function indexFromAuth(Request $request, Manager $fractal)
    {
        $company = $this->company($request);
        # get the company
        return $this->index($request, $fractal, $company->uuid);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'plan_id' => 'required|string',
            'reference' => 'required|string',
            'processor' => 'required|string',
            'currency' => 'required|string|size:3',
            'amount' => 'required|numeric',
            'is_successful' => 'required|numeric|in:0,1',
            'extra_data' => 'nullable|array'
        ]);
        #validate the request
        $company = \App\Models\Company::where('uuid', $id)->firstOrFail();
        # get the company
        $plan = Plan::where('uuid', $request->plan_id)->first();
        # get the plan information
        if (empty($plan)) {
            throw new RecordNotFoundException('Could not retrieve the plan information for the provided id.');
        }
        $billPayment = $company->billPayments()->create([
            'plan_id' => $plan->id,
            'reference' => $request->reference,
            'processor' => $request->processor,
            'currency' => $request->currency,
            'amount' => $request->amount,
            'json_data' => $request->input('extra_data', []),
            'is_successful' => (int) $request->is_successful
        ]);
        # create the model
        $resource = new Item($billPayment, new BillPaymentTransformer(), 'bill_payment');
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createFromAuth(Request $request, Manager $fractal)
    {
        $company = $this->company($request);
        # get the company
        return $this->create($request, $fractal, $company->uuid);
    }
}