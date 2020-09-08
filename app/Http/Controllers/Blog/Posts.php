<?php

namespace App\Http\Controllers\Blog;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ECommerce\Blog\Categories;
use App\Http\Controllers\ECommerce\Blog\Media;
use App\Models\Company;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class Posts extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new \App\Http\Controllers\ECommerce\Blog\Posts())->index($request, $fractal, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function post(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $postId = $request->query->get('id', null);
        # gets the post id
        if (empty($postId) && $request->has('slug')) {
            $postId = $company->blogPosts()->where('slug', $request->input('slug'))->pluck('uuid')->first();
        }
        return (new \App\Http\Controllers\ECommerce\Blog\Posts())->single($request, $fractal, $postId, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function categories(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new Categories())->index($request, $fractal, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function category(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $categoryId = $request->query->get('id', null);
        # gets the category id
        return (new Categories())->single($request, $fractal, $categoryId, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function media(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        return (new Media())->index($request, $fractal, $company);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleMedia(Request $request, Manager $fractal, string $id)
    {
        $company = Company::where('uuid', $id)->firstOrFail();
        # try to get the company
        $categoryId = $request->query->get('id', null);
        # gets the category id
        return (new Media())->single($request, $fractal, $categoryId, $company);
    }
}