<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Invite;
use League\Fractal\TransformerAbstract;

class InviteTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['inviter', 'inviting_user'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['inviting_user'];

    /**
     * @param Invite $invite
     *
     * @return array
     */
    public function transform(Invite $invite)
    {
        return [
            'id' => $invite->uuid,
            'email' => $invite->email,
            'firstname' => $invite->firstname,
            'lastname' => $invite->lastname,
            'message' => $invite->message,
            'config_data' => $invite->config_data,
            'status' => $invite->status,
            'updated_at' => !empty($invite->updated_at) ? $invite->updated_at->toIso8601String() : null,
            'created_at' => $invite->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param Invite $invite
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeInvitingUser(Invite $invite)
    {
        $user = $invite->inviting_user;
        if (empty($user)) {
            return null;
        }
        $transformer = (new UserTransformer())->setDefaultIncludes([])->setAvailableIncludes([]);
        return $this->item($user, $transformer, 'user');
    }
    
    /**
     * @param Invite $invite
     *
     * @return \League\Fractal\Resource\Item|null
     * @throws \ReflectionException
     */
    public function includeInviter(Invite $invite)
    {
        $reference = $invite->inviter;
        # get the model
        $reflection = new \ReflectionClass($reference);
        $transformer = 'App\\Transformers\\' . $reflection->getShortName() . 'Transformer';
        # we set the transformer name
        if (!class_exists($transformer)) {
            return null;
        }
        return $this->item($reference, new $transformer, snake_case($reflection->getShortName()));
    }
}