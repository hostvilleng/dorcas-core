<?php


namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\PayrollAuthorities;
use League\Fractal\TransformerAbstract;


class PayrollAuthorityTransformer extends TransformerAbstract
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
     * @param PayrollAuthorities $payrollAuthority
     *
     * @return array
     */

    public function transform(PayrollAuthorities $payrollAuthority){
        return array_merge([
            'embeds' => $this->getEmbeds(),
            'id' => $payrollAuthority->uuid,
            'name' => $payrollAuthority->authority_name,
            'payment_mode' => $payrollAuthority->payment_mode,
            'default_payment_details' => $payrollAuthority->default_payment_details,
            'payment_details' => $payrollAuthority->payment_details,
            'isActive' => $payrollAuthority->isActive,
            'updated_at' => !empty($payrollAuthority->updated_at) ? $payrollAuthority->updated_at->toIso8601String() : null,
            'created_at' => $payrollAuthority->created_at->toIso8601String(),
            'links' => [
                'self' => url('/payroll/authority', [$payrollAuthority->uuid])
            ],


        ]);
    }

    /**
     * @param PayrollAuthorities $payrollAuthority
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(PayrollAuthorities $payrollAuthority)
    {
        return $this->item($payrollAuthority->company, new CompanyTransformer(), 'company');
    }


}