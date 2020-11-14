<?php

namespace App\Http\Controllers\Store;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Invoicing\Product\Product;
use App\Http\Controllers\Invoicing\Product\ProductCategories;
use App\Models\Company;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class Products extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new \App\Http\Controllers\Invoicing\Product\Products())->index($request, $fractal, $company);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function product(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $productId = $request->query->get('id', null);
        # gets the product id
        return (new Product())->index($request, $fractal, $productId, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function categories(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new ProductCategories())->index($request, $fractal, $company);
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
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $categoryId = $request->query->get('id', null);
        # gets the category id
        return (new ProductCategories())->single($request, $fractal, $categoryId, $company);
    }
}