<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\BlogCategory;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class BlogCategoryTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'posts',
        'parent',
        'sub_categories'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['parent'];
    
    /**
     * @param BlogCategory $category
     *
     * @return array
     */
    public function transform(BlogCategory $category)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $category->uuid,
            'slug' => $category->slug,
            'name' => $category->name,
            'posts_count' => $category->posts()->count(),
            'updated_at' => !empty($category->updated_at) ? $category->updated_at->toIso8601String() : null,
            'created_at' => $category->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param BlogCategory $category
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeParent(BlogCategory $category)
    {
        if (empty($category->parent_id)) {
            return null;
        }
        return $this->item($category->parent, new BlogCategoryTransformer(), 'blog_category');
    }
    
    /**
     * @param BlogCategory      $category
     * @param ParamBag|null     $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includePosts(BlogCategory $category, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $posts = $category->posts()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($posts, new BlogPostTransformer(), 'blog_post');
    }
    
    /**
     * @param BlogCategory $category
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSubCategories(BlogCategory $category)
    {
        return $this->collection($category->subCategories, new BlogCategoryTransformer(), 'blog_category');
    }
}