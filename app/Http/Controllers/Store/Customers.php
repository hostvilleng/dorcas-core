<?php

namespace App\Http\Controllers\Store;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Transformers\CustomerTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Customers extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrCreate(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'firstname' => 'required|max:30',
            'lastname' => 'nullable|max:30',
            'email' => 'nullable|email|max:80',
            'phone' => 'nullable|max:30',
            'address' => 'nullable|max:250'
        ]);
        # validate the request
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $customer = $company->customers()->firstOrNew([
            'email' => $request->input('email'),
        ]);
        # create the model, if required
        if (empty($customer->uuid)) {
            $customer->firstname = $request->input('firstname');
            $customer->lastname = $request->input('lastname');
        }
        $customer->phone = $request->input('phone');
        $customer->address = $request->input('address');
        $customer->saveOrFail();
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        $data = $fractal->createData($resource)->toArray();
        $data['data']['orders_count'] = $customer->orders()->count();
        # appends the total number of orders by this person
        return response()->json($data, 201);
    }
}