<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApplicationInstall;
use League\Fractal\TransformerAbstract;

class ApplicationInstallTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'application',
        'company',
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['application', 'company'];
    
    /**
     * @param ApplicationInstall $install
     *
     * @return array
     */
    public function transform(ApplicationInstall $install)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $install->uuid,
            'extra_json' => (array) $install->extra_json,
            'updated_at' => !empty($install->updated_at) ? $install->updated_at->toIso8601String() : null,
            'created_at' => $install->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param ApplicationInstall $install
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeApplication(ApplicationInstall $install)
    {
        return $this->item($install->application, new ApplicationTransformer(), 'application');
    }
    
    /**
     * @param ApplicationInstall $install
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(ApplicationInstall $install)
    {
        return $this->item($install->company, new CompanyTransformer(), 'company');
    }
}