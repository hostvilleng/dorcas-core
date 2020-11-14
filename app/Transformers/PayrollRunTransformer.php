<?php


namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\PayrollRun;
use League\Fractal\TransformerAbstract;
use League\Fractal\ParamBag;


class PayrollRunTransformer extends TransformerAbstract
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
    protected $defaultIncludes = ['employees'];
    /**
     * @param PayrollRun $payrollRun
     *
     * @return array
     */

    public function transform(PayrollRun $payrollRun){
        return array_merge([
            'embeds' => $this->getEmbeds(),
            'id' => $payrollRun->uuid,
            'title' => $payrollRun->title,
            'run' => $payrollRun->run,
            'status' => $payrollRun->status,
            'updated_at' => !empty($payrollRun->updated_at) ? $payrollRun->updated_at->toIso8601String() : null,
            'created_at' => $payrollRun->created_at->toIso8601String(),
            'links' => [
                'self' => url('/payroll/runs', [$payrollRun->uuid])
            ],

        ]);
    }

    /**
     * @param PayrollRun $payrollRun
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(PayrollRun $payrollRun)
    {
        return $this->item($payrollRun->company, new CompanyTransformer(), 'company');
    }

    /**
     * @param PayrollRun $payrollRun
     * @param ParamBag $params
     *
     * @return \League\Fractal\Resource\Collection
     */

    public function includeEmployees(PayrollRun $payrollRun, ParamBag $params)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employees = $payrollRun->employees()->take($limit)
            ->offset($offset)
            ->oldest('firstname')
            ->oldest('lastname')
            ->get();
        return $this->collection($employees, new EmployeeTransformer(), 'employee');
    }


}