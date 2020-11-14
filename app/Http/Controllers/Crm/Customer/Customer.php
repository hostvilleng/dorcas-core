<?php

namespace App\Http\Controllers\Crm\Customer;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Crm\Deals\Deals;
use App\Transformers\CustomerTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Customer extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'phone' => 'phone',
        'email' => 'email',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $customer = $company->customers()->with(['contacts'])->where('uuid', $id)->firstOrFail();
        # try to get the customer
        if (!(clone $customer)->delete()) {
            throw new DeletingFailedException('Failed while deleting the customer');
        }
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     *
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $customer = $company->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
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
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'firstname' => 'nullable|max:30',
            'lastname' => 'nullable|max:30',
            'email' => 'nullable|email|max:80',
            'phone' => 'nullable|max:30',
        ]);
        # validate the request
        $customer = $company->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $this->updateModelAttributes($customer, $request);
        # update the attributes
        $customer->saveOrFail();
        # save the changes
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listDeals(Request $request, Manager $fractal, string $id)
    {
        $request->query->set('customer_ids', $id);
        return (new Deals())->index($request, $fractal);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createDeal(Request $request, Manager $fractal, string $id)
    {
        $request->request->set('customer_id', $id);
        return (new Deals())->create($request, $fractal);
    }
}