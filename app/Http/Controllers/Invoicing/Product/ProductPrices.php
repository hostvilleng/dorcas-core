<?php

namespace App\Http\Controllers\Invoicing\Product;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\ProductPriceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class ProductPrices extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'price' => 'unit_price',
        'currency' => 'currency'
    ];

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $this->validate($request, [
            'id' => 'required_without:ids|string',
            'ids' => 'required_without:id|array'
        ]);
        # validate the request
        $product = $company->products()->with(['prices'])->where('uuid', $id)->firstOrFail();
        # try to get the product
        $uuids = $request->has('ids') ? $request->input('ids') : [$request->input('id')];
        # our array of uuids to delete
        $filtered = $product->prices->whereNotIn('uuid', $uuids);
        # filter the list, and remove the requested ids
        if (!$product->prices()->whereIn('uuid', $uuids)->delete()) {
            # failed while deleting the currencies
            throw new DeletingFailedException(
                'Failed while trying to remove the requested currencies from the product.'
            );
        }
        $resource = new Collection($filtered, new ProductPriceTransformer(), 'price');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $company = $this->company($request);
        # retrieve the company
        $product = $company->products()->with(['prices'])->where('uuid', $id)->firstOrFail();
        # try to get the product
        $resource = new Collection($product->prices, new ProductPriceTransformer(), 'price');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'currency' => 'required|string|size:3',
            'price' => 'required|numeric',
        ]);
        # validate the request
        $product = $company->products()->where('uuid', $id)->firstOrFail();
        # try to get the product
        $currencies = (clone $product)->prices->map(function ($price) {
            return $price->currency;
        })->all();
        # get the existing currencies
        if (in_array($request->input('currency'), $currencies)) {
            throw new \UnexpectedValueException(
                'A price already exists for this currency. Are you trying to update it?'
            );
        }
        $isoCurrencies = new ISOCurrencies();
        # our currency context
        $currency = new Currency($request->input('currency'));
        if (!$currency->isAvailableWithin($isoCurrencies)) {
            # this currency is not available
            throw new \UnexpectedValueException(
                'The price currency is not a valid ISO currency. You provided a currency of: '.
                $request->input('currency')
            );
        }
        $product->prices()->create([
            'currency' => strtoupper($request->input('currency')),
            'unit_price' => $request->input('price')
        ]);
        # create the model
        $resource = new Collection($product->prices, new ProductPriceTransformer(), 'price');
        return response()->json($fractal->createData($resource)->toArray(), 201);
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
        $company = $this->company($request);
        # retrieve the company
        $this->validate($request, [
            'id' => 'required|string',
            'currency' => 'required_without:price|string|size:3',
            'price' => 'required_without:currency|numeric',
        ]);
        # validate the request
        $product = $company->products()->where('uuid', $id)->firstOrFail();
        # try to get the product
        $clone = clone $product;
        # clone the model
        $price = $clone->prices->where('uuid', $request->input('id'))->first();
        # get the specific price we're trying to update
        if (empty($price)) {
            # not found
            throw new RecordNotFoundException('We could not find a price with the provided id.');
        }
        $exists = $clone->prices->where('currency', strtoupper($request->input('currency')))->first();
        # we try to see if there's an entry for this currency already
        if (!empty($exists) && $exists->uuid !== $request->input('id')) {
            # the currency already exists
            throw new \UnexpectedValueException(
                'A price for the currency already exists on this product. You should try updating that one using '.
                'id value: '.$exists->uuid.'. (Additional - Currency: '.$exists->currency.'; Price: '.$exists->unit_price.')'
            );
        }
        $isoCurrencies = new ISOCurrencies();
        # our currency context
        $currency = new Currency($request->input('currency'));
        # instantiate the currency
        if (!$currency->isAvailableWithin($isoCurrencies)) {
            # this currency is not available
            throw new \UnexpectedValueException(
                'The price currency is not a valid ISO currency. You provided a currency of: '.
                $request->input('currency')
            );
        }
        $this->updateModelAttributes($price, $request);
        # update the attributes on the model
        $price->saveOrFail();
        # save the changes
        $resource = new Collection($product->prices, new ProductPriceTransformer(), 'price');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}