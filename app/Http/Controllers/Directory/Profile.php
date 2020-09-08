<?php

namespace App\Http\Controllers\Directory;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Profile extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $mode = $request->query('mode', 'professional');
        # the load mode
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = User::with(['professionalCredentials', 'professionalExperiences'])
                                ->when($mode === 'vendor', function ($query) {
                                    return $query->where('is_vendor', 1);
                                }, function ($query) {
                                    return $query->where('is_professional', 1);
                                })
                                ->oldest('firstname')
                                ->oldest('lastname')
                                ->paginate($limit);
            # get the customers
        } else {
            # searching for something
            $builder = User::search($search);
            if ($mode === 'vendor') {
                $builder =  $builder->where('is_vendor', 1);
            } else {
                $builder =  $builder->where('is_professional', 1);
            }
            $paginator = $builder->paginate($limit);
            $paginator->getCollection()->load('professionalCredentials', 'professionalExperiences');
        }
        $transformer = new UserTransformer();
        $transformer->setDefaultIncludes(['professional_credentials', 'professional_experiences']);
        $resource = new Collection($paginator->getCollection(), $transformer, 'user');
        # create the resource
        if (!empty($search)) {
            $pagingAppends['search'] = $search;
            # append the search term to the paginator
            $resource->setMetaValue('search', $search);
            # set the meta value if necessary
        }
        $paginator->appends($pagingAppends);
        # add the append terms
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request, Manager $fractal, string $id)
    {
        $user = User::with(['professionalCredentials', 'professionalExperiences'])->where('uuid', $id)->firstOrFail();
        # get the model
        $transformer = new UserTransformer();
        $transformer->setDefaultIncludes(['professional_credentials', 'professional_experiences', 'professional_services', 'vendor_services']);
        $resource = new Item($user, $transformer, 'user');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSocialConnections(Request $request, Manager $fractal)
    {
        $user = $request->user();
        # get the currently authenticated user
        $this->validate($request, [
            'index' => 'required|numeric'
        ]);
        # validate the request
        $configuration = $user->extra_configurations ?: [];
        # get the user's configurations
        if (empty($configuration['professional_social_contacts'])) {
            throw new RecordNotFoundException('There are no social connections on this profile to be deleted.');
        }
        unset($configuration['professional_social_contacts'][$request->index]);
        # unset the entry
        $user->extra_configurations = $configuration;
        # update the configuration
        $user->saveOrFail();
        # save the changes
        $resource = new Item($user, new UserTransformer(), 'user');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSocialConnections(Request $request, Manager $fractal)
    {
        $user = $request->user();
        # get the currently authenticated user
        $this->validate($request, [
            'channel' => 'required_without:channels|string',
            'id' => 'nullable|string',
            'url' => 'required_with:channel|url',
            'channels' => 'required_without:channel|array',
            'channels.*.channel' => 'required|string',
            'channels.*.id' => 'nullable|string',
            'channels.*.url' => 'required|string|url',
        ]);
        # validate the request
        $configuration = $user->extra_configurations ?: [];
        # get the user's configurations
        if ($request->has('channels')) {
            $channels = $request->input('channels', []);
        } else {
            $channels = [
                ['channel' => $request->channel, 'id' => $request->id, 'url' => $request->url]
            ];
        }
        if (empty($configuration['professional_social_contacts'])) {
            $configuration['professional_social_contacts'] = [];
        }
        $configuration['professional_social_contacts'] = array_values(array_merge($configuration['professional_social_contacts'], $channels));
        # merge both arrays
        $user->extra_configurations = $configuration;
        # update the configuration
        $user->saveOrFail();
        # save the changes
        $resource = new Item($user, new UserTransformer(), 'user');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}