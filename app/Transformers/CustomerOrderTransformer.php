<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\CustomerOrder;
use League\Fractal\TransformerAbstract;

class CustomerOrderTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['customer', 'order', 'transactions'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['transactions'];

    /**
     * @param CustomerOrder $customerOrder
     *
     * @return array
     */
    public function transform(CustomerOrder $customerOrder)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'customer_id' => $customerOrder->custoemr_id,
            'order_id' => $customerOrder->order_id,
            'invoice_number' => $customerOrder->invoice_number,
            'is_paid' => $customerOrder->is_paid,
            'paid_at' => !empty($customerOrder->paid_at) ? $customerOrder->paid_at->toIso8601String() : null
        ];
    }

    /**
     * @param CustomerOrder $customerOrder
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCustomer(CustomerOrder $customerOrder)
    {
        return $this->item($customerOrder->customer, new CustomerTransformer(), 'customer');
    }

    /**
     * @param CustomerOrder $customerOrder
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeOrder(CustomerOrder $customerOrder)
    {
        return $this->item($customerOrder->order, new OrderTransformer(), 'order');
    }

    /**
     * @param CustomerOrder $customerOrder
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeTransactions(CustomerOrder $customerOrder)
    {
        return $this->collection($customerOrder->transactions, new PaymentTransactionTransformer(), 'transactions');
    }
}