<?php

namespace App\Transformers;



use App\Dorcas\Common\APITransformerTrait;
use App\Models\ServiceRequest;
use League\Fractal\TransformerAbstract;

class ServiceRequestTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['company', 'service'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['company', 'service'];
    
    /**
     * @param ServiceRequest $request
     *
     * @return array
     */
    public function transform(ServiceRequest $request)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $request->uuid,
            'message' => $request->message,
            'attachment_url' => $request->attachment_full_url,
            'is_read' => $request->is_read,
            'status' => $request->status,
            'updated_at' => !empty($request->updated_at) ? $request->updated_at->toIso8601String() : null,
            'created_at' => $request->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param ServiceRequest $request
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(ServiceRequest $request)
    {
        return $this->item($request->company, new CompanyTransformer(), 'company');
    }
    
    /**
     * @param ServiceRequest $request
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeService(ServiceRequest $request)
    {
        $service = $request->service;
        if (empty($service)) {
            $service = $request->vendorService;
        }
        return $this->item($service, new ProfessionalServiceTransformer(), 'professional_service');
    }
}