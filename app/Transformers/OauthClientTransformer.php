<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use Hashids\Hashids;
use Laravel\Passport\Client;
use League\Fractal\TransformerAbstract;

class OauthClientTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * @param Client $client
     *
     * @return array
     */
    public function transform(Client $client)
    {
        $hashId = app(Hashids::class);
        # our hash id client
        $data = $client->toArray();
        if (is_numeric($data['id'])) {
            $data['id'] = $hashId->encode($data['id']);
        }
        $data['secret'] = $client->secret;
        return $data;
    }
}