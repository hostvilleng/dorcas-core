<?php

namespace App\Http\Controllers\Invoicing\Orders;


use App\Exceptions\RecordNotFoundException;
use App\Models\Company;
use App\Models\ProductPrice;
use Money\Currency;
use Ramsey\Uuid\Uuid;

trait OrderProcessingTrait
{
    /**
     * This method makes it possible to convert a products array from the request to the appropriate format for
     * writing to the database.
     *
     * @param Company       $company
     * @param array         $requestedProducts  the products array:[['id' => string, 'quantity' => integer], ...]
     * @param Currency|null $currency           the reference currency to use for finding the appropriate price
     * @param int           $total              the total cost of the requested products
     *
     * @return array
     */
    public function productsToOrderItems(Company $company, array $requestedProducts, Currency $currency = null, int &$total = 0)
    {
        $orderItems = [];
        # container for the products to be synced to the order as order items
        $currency = $currency ?: new Currency('NGN');
        # we set the default currency, if none is specified
        $productIds = collect($requestedProducts)->mapWithKeys(function ($product) {
            return [$product['id'] => ['qty' => $product['quantity'], 'price' => $product['price'] ?? -1]];
        })->all();
        # list of product ids
        $products = $company->products()->with(['prices'])->whereIn('uuid', array_keys($productIds))->get();
        # get the requested products
        foreach ($products as $product) {
            # loop through the individual products
            $info = $productIds[$product->uuid] ?? null;
            # get the product entry
            if (empty($info)) {
                continue;
            }
            $productPrice = null;
            # the price to use for the product
            if ($info['price'] === -1) {
                # no price was sent in the request
                foreach ($product->prices as $price) {
                    if ($price->currency !== $currency->getCode()) {
                        continue;
                    }
                    $productPrice = $price;
                    break;
                }
                if ($productPrice === null) {
                    # we could not find a matching price (based on currency) for this product
                    throw new RecordNotFoundException(
                        'No matching price for the '.$currency->getCode().' currency has been added for the product '.
                        $product->name.'. You should either add a price for the currency, or remove the product from the order.'
                    );
                }
            }
            $orderItems[$product->id] = [
                'uuid' => Uuid::uuid1()->toString(),
                'unit_price' => !empty($productPrice) ? $productPrice->unit_price : $info['price'],
                'quantity' => $productIds[$product->uuid]['qty']
            ];
            # our array of product items
            $total += $orderItems[$product->id]['quantity'] * $orderItems[$product->id]['unit_price'];
            # update the total price
        }
        return $orderItems;
    }
}