<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\BillPayment;
use League\Fractal\TransformerAbstract;

class BillPaymentTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'plan'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['plan'];

    /**
     * @param BillPayment $billPayment
     *
     * @return array
     */
    public function transform(BillPayment $billPayment)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'reference' => $billPayment->reference,
            'processor' => $billPayment->processor,
            'currency' => $billPayment->currency,
            'amount' => [
                'raw' => $billPayment->amount,
                'formatted' => number_format($billPayment->amount, 2)
            ],
            'extra_data' => $billPayment->json_data,
            'is_successful' => $billPayment->is_successful,
            'updated_at' => !empty($billPayment->updated_at) ? $billPayment->updated_at->toIso8601String() : null,
            'created_at' => $billPayment->created_at->toIso8601String()
        ];
    }

    /**
     * @param BillPayment $billPayment
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(BillPayment $billPayment)
    {
        return $this->item($billPayment->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param BillPayment $billPayment
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includePlan(BillPayment $billPayment)
    {
        return $this->item($billPayment->plan, new PlanTransformer(), 'plan');
    }
}