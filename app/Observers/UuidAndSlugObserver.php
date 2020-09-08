<?php

namespace App\Observers;


use App\Models\ApplicationCategory;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UuidAndSlugObserver
{
    /**
     * @param Model $model
     *
     * @throws \Exception
     */
    public function creating(Model $model)
    {
        $model->uuid = Uuid::uuid1()->toString();
        $string = '';
        if ($model instanceof BlogPost) {
            $string = $model->company_id . '-' . $model->title;
        } elseif ($model instanceof BlogCategory) {
            $string = $model->company_id . '-' . $model->name;
        } elseif ($model instanceof ApplicationCategory) {
            $string = $model->name;
        }
        if (!empty($string) && empty($model->slug)) {
            $model->slug = str_slug($string);
        }
    }
}