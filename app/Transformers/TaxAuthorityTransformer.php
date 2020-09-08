<?php


namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\TaxAuthority;
use League\Fractal\TransformerAbstract;

class TaxAuthorityTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    /**
     * @param TaxAuthority $taxAuthority
     *
     * @return array
     */

    public function transform(TaxAuthority $taxAuthority)
    {
        return array_merge([
            'embeds' => $this->getEmbeds(),
            'id' => $taxAuthority->uuid,
            'name' => $taxAuthority->authority_name,
            'payment_mode' => $taxAuthority->payment_mode,
            'default_payment_details' => $taxAuthority->default_payment_details,
            'payment_details' => $taxAuthority->payment_details,
            'isActive' => $taxAuthority->isActive,
            'updated_at' => !empty($taxAuthority->updated_at) ? $taxAuthority->updated_at->toIso8601String() : null,
            'created_at' => $taxAuthority->created_at->toIso8601String(),
            'links' => [
                'self' => url('/finance/tax/authority', [$taxAuthority->uuid])
            ],


        ]);
    }

    /**
     * @param TaxAuthority $taxAuthority
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(TaxAuthority $taxAuthority)
    {
        return $this->item($taxAuthority->company, new CompanyTransformer(), 'company');
    }




}