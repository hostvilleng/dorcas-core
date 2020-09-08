<?php


namespace App\Transformers;
use App\Dorcas\Common\APITransformerTrait;
use App\Models\TaxElements;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class TaxElementsTransformer extends TransformerAbstract
{

    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['taxAuthority'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['taxAuthority'];

    /**
     * @param TaxElements $taxElement
     *
     * @return array
     */

    public function transform(TaxElements $taxElement){
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $taxElement->uuid,
            'name' => $taxElement->element_name,
            'element_type' => $taxElement->element_type,
            'type_data' => $taxElement->type_data,
            'frequency' => $taxElement->frequency,
            'frequency_year' => $taxElement->frequency_year,
            'frequency_month'=> $taxElement->frequency_month,
            'target_accounts' => $taxElement->target_account,
            'isActive' => $taxElement->isActive,
            'updated_at' => !empty($taxElement->updated_at) ? $taxElement->updated_at->toIso8601String() : null,
            'created_at' => $taxElement->created_at->toIso8601String(),
            'links' => [
                'self' => url('/finance/tax/element', [$taxElement->uuid])
            ],

        ];
    }

    /**
     * @param TaxElements $taxElement
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeTaxAuthority(TaxElements $taxElement)
    {
        return $this->item($taxElement->taxAuthority, new TaxAuthorityTransformer(), 'taxAuthority');
    }

}