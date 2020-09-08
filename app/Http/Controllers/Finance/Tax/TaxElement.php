<?php

namespace App\Http\Controllers\Finance\Tax;

use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\TaxAuthorityTransformer;
use App\Transformers\TaxElementsTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App\Models\TaxElements;


class TaxElement extends Controller
{

    const TYPE = ['percentage','fixed'];
    const FREQUENCY = ['monthly','yearly'];
    private $taxElement;
    public function __construct(TaxElements $taxElements)
    {
        $this->taxElement = $taxElements;
    }


    protected $updateFields =
        [
            'element_name' => 'element_name',
            'element_type' => 'element_type',
            'frequency'=> 'frequency',
            'target_account' => 'target_account',
            'frequency_year' => 'frequency_month',
            'type_data' => 'type_data'

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
            'authority'=>'required|string',
            'element_name'=> 'required|string',
            'element_type' => ['required',Rule::in(self::TYPE),],
            'frequency' => ['required',Rule::in(self::FREQUENCY),],
            'frequency_month'=> 'required_if:frequency,monthly',
            'frequency_year'=> 'required_if:frequency,yearly',
            'type_data' => 'required',
            'target_accounts'=>'required|array',
            'target_accounts.*' => 'string'
        ]);
        $element = $company->TaxAuthority()->where('uuid',$request->authority)->firstOrFail();
        if (empty($element)) {
            throw new RecordNotFoundException('Could not find the specified authority to add the elements in.');
        }
        $tax_element = null;
        $data = [
            'element_name'=> $request->element_name,
            'element_type' => $request->element_type,
            'frequency' => $request->frequency,
            'type_data'  => json_encode($request->type_data),
            'target_account' =>  (array) $request->target_accounts,
        ];
        switch ($request->frequency){
            case 'monthly':
                $data['frequency_month'] = $request->frequency_month;
                break;
            case 'yearly':
                $data['frequency_year'] = $request->frequency_year;
                break;
        }
        $tax_element =  $element->elements()->create($data);
        $resource = new Item($tax_element, new TaxElementsTransformer());
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
            throw new RecordNotFoundException('Could not find the specified authority to search for elements in.');
        } else {
            $element = $company->TaxAuthority()->where('uuid', $id)->firstOrFail();
            if (empty($element)) {
                throw new RecordNotFoundException('Could not find the specified authority to search for elements in.');
            }
            $ids[] = $element->id;

            # the ids we're interested in
            $builder = $this->taxElement->whereIn('tax_authority_id', $ids);
        }

        $paginator = $builder->when($search, function ($query) use ($search) {
            return $query->where('element_name', 'like', '%'.$search.'%')
           ->orwhere('element_type', 'like', '%'.$search.'%');
        })
            ->latest()
            ->paginate($limit);

        $resource = new Collection($paginator->getCollection(), new TaxElementsTransformer());
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
        $element = $this->taxElement->where('uuid',$id)->firstorFail();
        # get the account
        $resource = new Item($element, new TaxElementsTransformer(), 'elements');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    public function update(Request $request, Manager $fractal, string $id){
        $this->validate($request, [
            'element_name'=> 'required|string',
            'element_type' => ['required',Rule::in(self::TYPE),],
            'frequency' => ['required',Rule::in(self::FREQUENCY),],
            'frequency_month'=> 'nullable|required_if:frequency,monthly',
            'frequency_year'=> 'nullable|required_if:frequency,yearly',
            'type_data' => 'required',
            'target_account'=>'required|array',
            'target_account.*' => 'string'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $element = $this->taxElement->where('uuid',[$id])->firstOrFail();

        if ($request->has('authority')) {
            $tax_authority= $company->TaxAuthority()->where('uuid', $request->authority)->first();
            if (empty($tax_authority)) {
                throw new RecordNotFoundException('Could not find the specified authority  to add the element in.');
            }
            $element->tax_authority_id = $tax_authority->id;
        }


        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($element, $request);
        # update the attributes
        $element->saveOrFail();
        # save the changes
        $resource = new Item($element, new TaxElementsTransformer(), 'taxAuthority');
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
        $element = $this->taxElement->where('uuid',[$id])->firstOrFail();

        # get the entry
        if (!(clone $element)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified Tax Element Please try again.'
            );
        }
        $transformer = new TaxElementsTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($element, $transformer, 'taxElement');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }


}