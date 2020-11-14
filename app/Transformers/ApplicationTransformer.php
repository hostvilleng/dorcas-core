<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Application;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ApplicationTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'categories',
        'installs',
        'oauth_client',
        'user'
    ];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['categories', 'oauth_client'];
    
    /** @var array  */
    protected $installedAppIds = [];
    
    /** @var bool  */
    protected $defaultInstallState = false;
    
    /**
     * ApplicationTransformer constructor.
     *
     * @param array $installedAppIds
     * @param bool  $defaultInstallState
     */
    public function __construct(array $installedAppIds = [], bool $defaultInstallState = false)
    {
        $this->installedAppIds = $installedAppIds;
        $this->defaultInstallState = $defaultInstallState;
    }
    
    /**
     * @param Application $application
     *
     * @return array
     */
    public function transform(Application $application)
    {
        $installsCount = $application->installs_count !== null ? $application->installs_count : $application->installs()->count();
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $application->uuid,
            'name' => $application->name,
            'type' => $application->type,
            'description' => $application->description,
            'homepage_url' => $application->homepage_url,
            'icon_url' => $application->icon ?: cdn('/images/app_store_default.jpg'),
            'banner_url' => $application->banner,
            'billing_type' => $application->billing_type,
            'billing_period' => $application->billing_period,
            'billing_currency' => $application->billing_currency,
            'billing_price' => [
                'raw' => $application->billing_price,
                'formatted' => number_format($application->billing_price, 2)
            ],
            'installs_count' => $installsCount,
            'is_installed' => (!empty($this->installedAppIds) && in_array($application->id, $this->installedAppIds)) || $this->defaultInstallState, // only relevant in the app store context
            'is_published' => $application->is_published,
            'is_free' => $application->is_free,
            'extra_json' => (array) $application->extra_json,
            'published_at' => !empty($application->published_at) ? $application->published_at->toIso8601String() : null,
            'updated_at' => !empty($application->updated_at) ? $application->updated_at->toIso8601String() : null,
            'created_at' => $application->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param Application $application
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCategories(Application $application)
    {
        if (empty($application->categories)) {
            return null;
        }
        return $this->collection($application->categories, new ApplicationCategoryTransformer(), 'category');
    }
    
    /**
     * @param Application $application
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includeOauthClient(Application $application)
    {
        $request = app()->make('request');
        $user = $request->user();
        if (empty($user) || $user->id !== $application->user_id) {
            # either not signed in, or the application doesn't belong to the requester
            return null;
        }
        return $this->item($application->oauthClient, new OauthClientTransformer(), 'oauth_client');
    }
    
    /**
     * @param Application   $application
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeInstalls(Application $application, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $installs = $application->installs()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($installs, new ApplicationInstallTransformer(), 'application_install');
    }
    
    /**
     * @param Application $application
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Application $application)
    {
        return $this->item($application->user, new UserTransformer(), 'user');
    }
}