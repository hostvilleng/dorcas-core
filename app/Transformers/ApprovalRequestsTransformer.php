<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApprovalRequests;
use App\Models\Approvals;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ApprovalRequestsTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['approvals'];



    /**
     * @param Product $product
     *
     * @return array
     */
    public function transform(ApprovalRequests $approval_request)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $approval_request->uuid,
            'approval_status' => $approval_request->approval_status,
            'approval_comments' => $approval_request->request_status,
            'model' => $approval_request->model,
            'model_id' => $approval_request->model_request_id,
            'model_data' => json_decode($approval_request->model_data,true),
            'updated_at' => $approval_request->updated_at->toIso8601String(),
            'created_at' => $approval_request->created_at->toIso8601String(),
            'links' => [
                'self' => url('/approval-request', [$approval_request->uuid])
            ]
        ];
        return $resource;
    }
    
    /**
     * @param ApprovalRequests $approval_request
     *
     * @return \League\Fractal\Resource\Collection
     */


    public function includeApprovals(ApprovalRequests $approval_request, ParamBag $params)
    {
//        return $this->item($approval_request->approvals, new ApprovalsTransformer(), 'approval');

        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $approvals = $approval_request->approvals()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($approvals, new ApprovalsTransformer(), 'approvals');
    }



}