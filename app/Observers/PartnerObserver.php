<?php

namespace App\Observers;


use App\Models\Partner;
use Ramsey\Uuid\Uuid;

class PartnerObserver
{
    /**
     * @param Partner $model
     *
     * @throws \Exception
     */
    public function creating(Partner $model)
    {
        $model->uuid = Uuid::uuid1()->toString();
        if (empty($model->slug)) {
            $model->slug = str_slug($model->name);
        }
    }
}