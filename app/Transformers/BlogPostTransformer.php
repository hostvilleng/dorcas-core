<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\BlogPost;
use League\Fractal\TransformerAbstract;

class BlogPostTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'categories',
        'media',
        'posted_by'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['categories', 'media', 'posted_by'];
    
    /**
     * @param BlogPost $post
     *
     * @return array
     */
    public function transform(BlogPost $post)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $post->uuid,
            'slug' => $post->slug,
            'title' => $post->title,
            'summary' => $post->summary,
            'content' => $post->content,
            'is_published' => $post->is_published,
            'publish_at' => !empty($post->publish_at) ? $post->publish_at->toIso8601String() : null,
            'featured_at' => !empty($post->featured_at) ? $post->featured_at->toIso8601String() : null,
            'updated_at' => !empty($post->updated_at) ? $post->updated_at->toIso8601String() : null,
            'created_at' => $post->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param BlogPost $post
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeMedia(BlogPost $post)
    {
        if (empty($post->media_id)) {
            return null;
        }
        return $this->item($post->media, new BlogMediaTransformer(), 'media');
    }
    
    /**
     * @param BlogPost $post
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCategories(BlogPost $post)
    {
        return $this->collection($post->categories, new BlogCategoryTransformer(), 'category');
    }
    
    /**
     * @param BlogPost $post
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includePostedBy(BlogPost $post)
    {
        $poster = $post->poster;
        # get the posting entity
        try {
            $reflection = new \ReflectionClass(get_class($poster));
            $transformer = 'App\\Transformers\\' . $reflection->getShortName() . 'Transformer';
            return $this->item($poster, new $transformer(), 'posted_by');
        
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}