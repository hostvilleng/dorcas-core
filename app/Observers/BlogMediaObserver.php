<?php

namespace App\Observers;


use App\Models\BlogMedia;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class BlogMediaObserver
{
    /**
     * @param BlogMedia $model
     */
    public function creating(BlogMedia $model)
    {
        $model->uuid = Uuid::uuid1()->toString();
    }
    
    /**
     * @param BlogMedia $model
     */
    public function deleting(BlogMedia $model)
    {
        if ($model->type === 'image') {
            Storage::disk(config('filesystems.default'))->delete($model->filename);
        }
    }
}