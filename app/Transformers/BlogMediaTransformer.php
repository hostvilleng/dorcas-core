<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\BlogMedia;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class BlogMediaTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'posts'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [];
    
    /**
     * @param BlogMedia $media
     *
     * @return array
     */
    public function transform(BlogMedia $media)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $media->uuid,
            'title' => $media->title,
            'url' => $media->type === 'image' ? $media->file_url : $media->filename,
            'posts_count' => $media->posts()->count(),
            'updated_at' => !empty($media->updated_at) ? $media->updated_at->toIso8601String() : null,
            'created_at' => $media->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param BlogMedia     $media
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includePosts(BlogMedia $media, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $posts = $media->posts()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($posts, new BlogPostTransformer(), 'blog_post');
    }
}