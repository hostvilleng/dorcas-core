<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\UserAccessGrant;
use League\Fractal\TransformerAbstract;

class UserAccessGrantTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['user', 'company'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    
    /**
     * @param UserAccessGrant $userAccessGrant
     *
     * @return array
     */
    public function transform(UserAccessGrant $userAccessGrant)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $userAccessGrant->uuid,
            'status' => $userAccessGrant->status,
            'extra_json' => $userAccessGrant->extra_json,
            'url' => $userAccessGrant->access_url,
            'status_updated_at' => !empty($userAccessGrant->status_updated_at) ?
                $userAccessGrant->status_updated_at->toIso8601String() : null,
            'created_at' => $userAccessGrant->created_at->toIso8601String(),
        ];
    }
    
    /**
     * @param UserAccessGrant $userAccessGrant
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(UserAccessGrant $userAccessGrant)
    {
        return $this->item($userAccessGrant->company, new CompanyTransformer(), 'company');
    }
    
    /**
     * @param UserAccessGrant $userAccessGrant
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(UserAccessGrant $userAccessGrant)
    {
        return $this->item($userAccessGrant->user, new UserTransformer(), 'user');
    }
}