<?php

namespace App\Http\Controllers\Invoicing\Orders;


use App\Http\Controllers\Controller;
use App\Transformers\CustomerTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class OrderCustomers extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'id' => 'required|string'
        ]);
        # validate the request
        $company = $this->company($request);
        # retrieve the company
        $order = $company->orders()->withCount(['customers'])->where('uuid', $id)->firstOrFail();
        # try to get the order
        $customer = $order->customers()->where('uuid', $request->input('id'))->firstOrFail();
        # get the customer
        if ($order->customers_count === 1) {
            # there's just one customer on the order -- removing the customer is the same as deleting the order
            throw new \UnexpectedValueException(
                'There is just one customer on the order, and it is not possible to remove the customer. You should '.
                'instead delete the order.'
            );
        }
        $order->customers()->detach($customer->id);
        # remove the customer from the list
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $order = $company->orders()->where('uuid', $id)->firstOrFail();
        # try to get the order
        $paginator = $order->customers()->when($search, function ($query) use ($search) {
                                            return $query->where('firstname', 'like', '%'.$search.'%')
                                                        ->orWhere('lastname', 'like', '%'.$search.'%')
                                                        ->orWhere('phone', 'like', '%'.$search.'%')
                                                        ->orWhere('email', 'like', '%'.$search.'%');
                                        })
                                        ->oldest('firstname')
                                        ->oldest('lastname')
                                        ->paginate($limit);
        # get the products
        $resource = new Collection($paginator->getCollection(), new CustomerTransformer(), 'customer');
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
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'id' => 'required|string',
            'is_paid' => 'required|in:0,1,yes,no,true,false'
        ]);
        # validate the request
        $company = $this->company($request);
        # retrieve the company
        $order = $company->orders()->where('uuid', $id)->firstOrFail();
        # try to get the order
        $customer = $order->customers()->where('uuid', $request->input('id'))->firstOrFail();
        # get the customer
        $isPaid = in_array($request->is_paid, [1, 'true', true, 'yes']) ? 1 : 0;
        # set the isPaid value
        $customerOrder = $customer->pivot;
        # get the actual pivot
        $customerOrder->is_paid = $isPaid;
        if ($isPaid === 1) {
            $customerOrder->paid_at = Carbon::now()->format('Y-m-d H:i:s');
        }
        if (!$customerOrder->save()) {
            # no affected rows
            throw new \RuntimeException('The customer order could not be updated.');
        }
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}