<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\ApprovalAuthorizers;
use App\Models\Approvals;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ApprovalsTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['authorizers'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['authorizers'];

    /**
     * @param Approvals $approval

     *
     * @return array
     */
    public function transform(Approvals $approval)
    {
        $name =  $approval->title;
        $resource = [
            'embeds' => $this->getEmbeds(),
            'id' => $approval->uuid,
            'title' => $name,
            'scope_type' => $approval->scope_type,
            'frequency_type' => $approval->frequency_type,
            'active' => $approval->active,
            'scope_data' => $approval->scope_data,
            'updated_at' => $approval->updated_at->toIso8601String(),
            'created_at' => $approval->created_at->toIso8601String(),
            'links' => [
                'self' => url('/approvals', [$approval->uuid])
            ],
        ];
        return $resource;
    }
    


    /**
     * @param Approvals $approval
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(Approvals $approval)
    {
        return $this->item($approval->company, new CompanyTransformer(), 'company');
    }

    public function includeAuthorizers(Approvals $approval, ParamBag $params){
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $authorizers = $approval->authorizers()->take($limit)
            ->offset($offset)
            ->get();
        foreach ($authorizers as $authorizer){
            $authorizer->approval_scope = $authorizer->pivot->approval_scope;
        }
        return $this->collection($authorizers, new UserTransformer(), 'authorizers');
    }


}