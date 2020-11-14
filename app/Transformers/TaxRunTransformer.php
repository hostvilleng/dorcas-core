<?php


namespace App\Transformers;
use App\Dorcas\Common\APITransformerTrait;
use App\Models\TaxRuns;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class TaxRunTransformer extends TransformerAbstract
{

    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['taxElement'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['taxElement'];

    /**
     * @param TaxRuns $taxRun
     *
     * @return array
     */

    public function transform(TaxRuns $taxRun){
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $taxRun->uuid,
            'name' => $taxRun->run_name,
            'isActive' => ($taxRun->isActive? 'True': 'false'),
            'status' =>$taxRun->status,
            'updated_at' => !empty($taxRun->updated_at) ? $taxRun->updated_at->toIso8601String() : null,
            'created_at' => $taxRun->created_at->toIso8601String(),
            'links' => [
                'self' => url('/finance/tax/run', [$taxRun->uuid])
            ],
        ];
    }

    /**
     * @param TaxRuns $taxRun
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeTaxElement(TaxRuns $taxRun)
    {
        return $this->item($taxRun->taxElement, new TaxElementsTransformer(), 'taxElement');
    }

}