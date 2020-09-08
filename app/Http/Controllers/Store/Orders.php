<?php

namespace App\Http\Controllers\Store;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Invoicing\Orders\Orders as InvoicingOrders;
use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class Orders extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function checkout(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new InvoicingOrders())->create($request, $fractal, $company);
    }
}