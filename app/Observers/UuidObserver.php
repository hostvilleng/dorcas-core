<?php

namespace App\Observers;


use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;

class UuidObserver
{
    /**
     * @param Model $model
     */
    public function creating(Model $model)
    {
        if (in_array('uuid', $model->getFillable())) {
            $model->uuid = Uuid::uuid1()->toString();
        } elseif ($model instanceof Role || $model instanceof Permission ||$model instanceof \Spatie\Permission\Models\Role) {
            $model->uuid = Uuid::uuid1()->toString();
        }
    }
}