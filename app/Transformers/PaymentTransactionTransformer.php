<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\PaymentTransaction;
use League\Fractal\TransformerAbstract;

class PaymentTransactionTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['customer', 'order'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return array
     */
    public function transform(PaymentTransaction $paymentTransaction)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $paymentTransaction->uuid,
            'channel' => $paymentTransaction->channel,
            'amount' => $paymentTransaction->amount,
            'currency' => $paymentTransaction->currency,
            'reference' => $paymentTransaction->reference,
            'response_code' => $paymentTransaction->response_code,
            'response_description' => $paymentTransaction->response_description,
            'json_payload' => $paymentTransaction->json_payload,
            'is_successful' => $paymentTransaction->is_successful,
            'is_trashed' => $paymentTransaction->deleted_at !== null,
            'trashed_at' => !empty($paymentTransaction->deleted_at) ? $paymentTransaction->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($paymentTransaction->updated_at) ? $paymentTransaction->updated_at->toIso8601String() : null,
            'created_at' => $paymentTransaction->created_at->toIso8601String()
        ];
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCustomer(PaymentTransaction $paymentTransaction)
    {
        return $this->item($paymentTransaction->customer, new CustomerTransformer(), 'customer');
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeOrder(PaymentTransaction $paymentTransaction)
    {
        return $this->item($paymentTransaction->order, new OrderTransformer(), 'order');
    }
}