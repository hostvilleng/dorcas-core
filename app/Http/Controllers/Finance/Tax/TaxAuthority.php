<?php

namespace App\Http\Controllers\Finance\Tax;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\TaxAuthorityTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class TaxAuthority extends Controller
{

    const PAYMENT_MODE = ['flutterwave','paystack'];
    public function __construct()
    {
    }


    protected $updateFields =
        [
        'authority_name'=>'authority_name',
        'payment_mode'=>'payment_mode',
        'payment_details' => 'payment_details',
        'default_payment_details' => 'default_payment_details'

    ];

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create( Request $request, Manager $fractal){
        $company = $this->company($request);
        # get the currently authenticated company
        $this->validate($request,[
            'authority_name'=> 'required|string',
            'payment_mode' => ['required',Rule::in(self::PAYMENT_MODE),],
            'default_payment_details' => 'required',
        ]);
        $tax_authority = null;
        $data = [
            'authority_name'=> $request->authority_name,
            'payment_mode' => $request->payment_mode,
            'default_payment_details'  => json_encode($request->default_payment_details),
        ];
        if($request->has('payment_details')){
            $data['payment_details']  = json_encode($request->payment_details);

        }
        $tax_authority = $company->TaxAuthority()->create($data);
        $resource = new Item($tax_authority, new TaxAuthorityTransformer());
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function search(Request $request, Manager $fractal){
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->TaxAuthority()->when($search, function ($query) use ($search) {
            return $query->where('authority_name', 'like', '%'.$search.'%');
        })
            ->oldest('authority_name')
            ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), (new TaxAuthorityTransformer()));
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
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function single(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $authority = $company->TaxAuthority()->where('uuid', $id)->firstOrFail();
        # get the account
        $resource = new Item($authority, new TaxAuthorityTransformer(),'taxAuthority');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */

    public function update(Request $request, Manager $fractal, string $id){
        $this->validate($request, [
            'authority_name'=> 'nullable|string',
            'payment_mode' => ['nullable',Rule::in(self::PAYMENT_MODE),],
            'default_payment_details' => 'required',
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $authority = $company->TaxAuthority()->where('uuid',[$id])->firstOrFail();
        # get the entry

        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($authority, $request);
        # update the attributes
        $authority->saveOrFail();
        # save the changes
        $resource = new Item($authority, new TaxAuthorityTransformer(), 'taxAuthority');
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
        $authority = $company->TaxAuthority()->where('uuid',[$id])->firstOrFail();

        # get the entry
        if (!(clone $authority)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified Tax Authority. Please try again.'
            );
        }
        $transformer = new TaxAuthorityTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($authority, $transformer, 'taxAuthority');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

}