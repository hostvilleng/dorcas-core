<?php


namespace App\Transformers;
use App\Dorcas\Common\APITransformerTrait;
use App\Models\PayrollAllowances;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PayrollAllowancesTransformer extends TransformerAbstract
{

    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['payrollAuthority','paygroup'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['payrollAuthority','paygroup'];

    /**
     * @param payrollAllowances $payrollAllowance
     *
     * @return array
     */

    public function transform(PayrollAllowances $payrollAllowance){

        $resource =  [
            'embeds' => $this->getEmbeds(),
            'id' => $payrollAllowance->uuid,
            'name' => $payrollAllowance->allowance_name,
            'allowance_type' => $payrollAllowance->allowance_type,
            'model' => $payrollAllowance->model,
            'model_data' => $payrollAllowance->model_data,
            'isActive' => $payrollAllowance->isActive,
            'updated_at' => !empty($payrollAllowance->updated_at) ? $payrollAllowance->updated_at->toIso8601String() : null,
            'created_at' => $payrollAllowance->created_at->toIso8601String(),
            'links' => [
                'self' => url('/payroll/allowance', [$payrollAllowance->uuid])
            ]
        ];
        if($payrollAllowance->payroll_authority_id){
            $resource['authority_id'] = $payrollAllowance->payroll_authority_id;
            $resource['authority_name'] = $payrollAllowance->authorities->authority_name;
        }
        return $resource;

    }

    /**
     * @param PayrollAllowances $payrollAllowance
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includePayrollAuthority(PayrollAllowances $payrollAllowance)
    {
        if($payrollAllowance->authorities == null){
            return null;
        }
        return $this->item($payrollAllowance->authorities, new PayrollAuthorityTransformer(), 'payrollAuthority');
    }

    /**
     * @param PayrollAllowances $allowances
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includePaygroup(PayrollAllowances $allowances, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $groups = $allowances->PayrollGroup()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($groups, new PayrollGroupTransformer(), 'paygroup');
    }

}