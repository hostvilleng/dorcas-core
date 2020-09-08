<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApprovalAuthorizers;
use App\Models\ApprovalRequests;
use App\Models\Approvals;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ApprovalAuthorizersTransformer extends TransformerAbstract
{
    use APITransformerTrait;

    /**
     * Resources that can be included if requested.
     *
     * @var array
//     */
    protected $availableIncludes = ['employees'];
//
//    /**
//     * Include resources without needing it to be requested.
//     *
//     * @var array
//     */
    protected $defaultIncludes = ['employees','approvals'];



    /**
     * @param ApprovalAuthorizers $authorizer
     *
     * @return array
     */
    public function transform(ApprovalAuthorizers $authorizer)
    {
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $authorizer->uuid,
            'approval_scope' => $authorizer->approval_scope,
            'updated_at' => $authorizer->updated_at->toIso8601String(),
            'created_at' => $authorizer->created_at->toIso8601String(),
            'links' => [
                'self' => url('/authorizer', [$authorizer->uuid])
            ]
        ];
        return $resource;
    }

    /**
     * @param ApprovalAuthorizers $authorizer
     *
     * @return \League\Fractal\Resource\Collection
     */


    public function includeApprovals(ApprovalAuthorizers $authorizer,ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $approval  = $authorizer->approval()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($approval, new ApprovalsTransformer(), 'approvals');
//        return $this->collection($authorizer->approvals, new ApprovalsTransformer(), 'approvals');
    }

//
    public function includeEmployees(ApprovalAuthorizers $authorizer, ParamBag $params = null)
    {
//        return $this->item($authorizer->employees, new EmployeeTransformer(), 'employees');
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $employee = $authorizer->employees()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($employee, new EmployeeTransformer(), 'employees');


    }



}