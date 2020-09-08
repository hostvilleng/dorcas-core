<?php

namespace App\Http\Controllers\Developers;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationCategory;
use App\Models\ApplicationInstall;
use App\Models\User;
use App\Transformers\ApplicationTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\ClientRepository;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Applications extends Controller
{
    protected $updateFields = [
        'name' => 'name',
        'type' => 'type',
        'description' => 'description',
        'homepage_url' => 'homepage_url',
        'icon' => 'icon_filename',
        'banner' => 'banner_filename',
        'billing_type' => 'billing_type',
        'billing_period' => 'billing_period',
        'billing_currency' => 'billing_currency',
        'billing_price' => 'billing_price',
        'extra_json' => 'extra_json',
        'is_published' => 'is_published',
        'is_free' => 'is_free',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $category = null;
        # get the category
        if ($request->has('category_slug')) {
            $category = ApplicationCategory::where('slug', $request->input('category_slug'))->first();
            # set the category
        }
        $user = null;
        if ($request->has('user_id')) {
            $user = User::where('uuid', $request->input('user_id'))->first();
        }
        # filter by user id
        $paginator = Application::withCount(['installs'])
                                ->with(['categories'])
                                ->when($category, function ($query) use ($category) {
                                    return $query->whereIn('id', function ($query) use ($category) {
                                        $query->select('application_id')
                                            ->from('application_category')
                                            ->where('application_category_id', $category->id);
                                    });
                                })
                                ->when($user, function ($query) use ($user) {
                                    return $query->where('user_id', $user->id);
                                })
                                ->oldest('name')
                                ->paginate($limit);
        # get the applications
        $resource = new Collection($paginator->getCollection(), new ApplicationTransformer(), 'application');
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
     * @param Request          $request
     * @param Manager          $fractal
     * @param ClientRepository $passport
     * @param string|null      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, Manager $fractal, ClientRepository $passport, string $id = null)
    {
        $this->validate($request, [
            'name' => (!empty($id) ? 'nullable' : 'required') . '|max:80',
            'type' => (!empty($id) ? 'nullable' : 'required') . '|string|in:mobile,web,desktop,cli,others',
            'description' => (!empty($id) ? 'nullable' : 'required') . '|string',
            'homepage_url' => (!empty($id) ? 'nullable' : 'required') . '|url',
            'icon' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:5120',
            'billing_type' => 'nullable|string|in:"one time",subscription',
            'billing_period' => 'nullable|string|in:weekly,monthly,yearly',
            'billing_currency' => 'nullable|string|size:3',
            'billing_price' => 'nullable|numeric',
            'is_published' => 'nullable|numeric|in:0,1',
            'is_free' => 'nullable|numeric|in:0,1',
            'extra_json' => 'nullable|array',
        ]);
        # validate the request
        $user = $request->user();
        # get the currently logged in user
        $application = null;
        if (!empty($id)) {
            $application = $user->applications()->where('uuid', $id)->first();
            # try to pick up the application, it it exists
        }
        if (empty($application)) {
            # we still have an empty model at this point -- create it
            $application = new Application(['user_id' => $user->id]);
        }
        if ($request->has('icon')) {
            # we're uploading a new icon
            if (!empty($application->icon_filename)) {
                Storage::disks(config('filesystems.default'))->delete($application->icon_filename);
            }
            $request->request->set('icon', $request->file('icon')->store('developers-' . $user->uuid . '/icons'));
        }
        if ($request->has('banner')) {
            if (!empty($application->banner_filename)) {
                Storage::disks(config('filesystems.default'))->delete($application->banner_filename);
            }
            $request->request->set('banner', $request->file('banner')->store('developers-' . $user->uuid . '/banners'));
        }
        if (empty($application->uuid)) {
            # since we're crafting a new app - we issue API keys to it.
            $client = $passport->createPasswordGrantClient(
                null, $request->input('name'), $request->input('homepage_url')
            );
            # create the client
            $application->oauth_client_id = $client->id;
            # set the oauth client ID
        }
        if ($request->has('is_published') && $request->input('is_published') == 1) {
            $application->published_at = Carbon::now()->format('Y-m-d H:i:s');
        }
        $this->updateModelAttributes($application, $request);
        # update the application
        $application->save();
        # update the application
        $resource = new Item($application, new ApplicationTransformer(), 'application');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray(), !empty($id) ? 200 : 201);
    }
    
    /**
     * @param Request          $request
     * @param Manager          $fractal
     * @param ClientRepository $passport
     * @param string           $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, ClientRepository $passport, string $id)
    {
        $user = $request->user();
        # get the currently logged in user
        $application = $user->applications()->withCount(['installs'])->where('uuid', $id)->firstOrFail();
        # try to get the application
        if ($application->installs_count > 0) {
            # this app has active installs
            throw new DeletingFailedException(
                'This application is in active use by at least '.$application->installs_count. ' businesses. '.
                'You can unpublish it so it does not show up for more people to install in the future.'
            );
        }
        if (!empty($application->icon_filename)) {
            Storage::disks(config('filesystems.default'))->delete($application->icon_filename);
        }
        if (!empty($application->banner_filename)) {
            Storage::disks(config('filesystems.default'))->delete($application->banner_filename);
        }
        $client = $application->oauthClient;
        # get the oauth client
        $passport->delete($client);
        # delete the client, and revoke all issued tokens on the client.
        if (!(clone $application)->delete()) {
            throw new DeletingFailedException('Failed while deleting the application');
        }
        $transformer = new ApplicationTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # configure the transformer
        $resource = new Item($application, $transformer, 'application');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request, Manager $fractal, string $id)
    {
        $application = Application::withCount(['installs'])->with(['categories'])->where('uuid', $id)->firstOrFail();
        # get the application
        $resource = new Item($application, new ApplicationTransformer(), 'application');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * Returns the periods to use for the search.
     *
     * @param Carbon|null $date1
     * @param Carbon|null $date2
     *
     * @return array
     */
    protected function getStatsTimeRange(Carbon $date1 = null, Carbon $date2 = null): array
    {
        $date1 = empty($date1) ? Carbon::now()->startOfMonth() : $date1;
        $date2 = empty($date2) ? Carbon::now()->endOfMonth() : $date2;
        $period1 = $date1 < $date2 ? $date1 : $date2;
        $period2 = $date1 < $date2 ? $date2 : $date1;
        return [$period1, $period2];//
    }
    
    /**
     * We get all dates within the period.
     *
     * @param Carbon $from
     * @param Carbon $to
     *
     * @return array
     */
    protected function fillStatDates(Carbon $from, Carbon $to)
    {
        $dates = [];
        # the dates array
        while ($to->greaterThanOrEqualTo($from)) {
            # we loop till we get the last date
            $dates[$from->format('Y-m-d')] = 0;
            $from = $from->addDay(1);
        }
        return $dates;
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstallStats(Request $request, Manager $fractal)
    {
        $data = [];
        # the response container
        $ranges = $this->getStatsTimeRange();
        # get the time ranges
        $appIds = $request->input('application_ids', []);
        # the application IDs to query for
        $applications = $request->user()->applications()->withCount(['installs'])
                                                        ->with(['categories'])
                                                        ->when($appIds, function ($query) use ($appIds) {
                                                            return $query->whereIn('uuid', $appIds);
                                                        })
                                                        ->oldest('name')
                                                        ->get();
        # get the applications
        $resource = new Collection($applications, new ApplicationTransformer(), 'application');
        $data['applications'] = $fractal->createData($resource)->toArray();
        # convert it to an array
        if (!empty($applications)) {
            # we can pull up stats
            $data['stats']['total_installs'] = collect($data['applications']['data'])->sum('installs_count');
            $data['stats']['total_apps'] = $applications->count();
            # set initial data
            $installs = ApplicationInstall::whereIn('application_id', $applications->pluck('id'))
                                            ->whereBetween('created_at', [$ranges[0]->format('Y-m-d H:i:s'), $ranges[1]->format('Y-m-d H:i:s')])
                                            ->oldest('application_id')
                                            ->oldest('created_at')
                                            ->get();
            # get the install information
            $apps = $applications->mapWithKeys(function ($app) {
                return [$app->id => $app->uuid];
            });
            # we create a dictionary
            foreach ($apps as $id => $uuid) {
                $data['stats']['installs'][$uuid] = [
                    'total' => 0,
                    'from' => $ranges[0]->format('Y-m-d'),
                    'to' => $ranges[1]->format('Y-m-d'),
                    'data' => $this->fillStatDates($ranges[0], $ranges[1])
                ];
                # the installs between periods;
            }
            foreach ($installs as $install) {
                # looping through
                $uuid = $apps[$install->application_id] ?: null;
                # we try to get the app UUID
                if (empty($uuid)) {
                    continue;
                }
                $data['stats']['installs'][$uuid]['total'] += 1;
                # increment the count
                $data['stats']['installs'][$uuid]['data'][$install->created_at->format('Y-m-d')] += 1;
                # add the install record
            }
        }
        return response()->json($data);
    }
}