<?php

namespace App\Http\Controllers\Developers;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationCategory;
use App\Transformers\ApplicationInstallTransformer;
use App\Transformers\ApplicationTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class AppStore extends Controller
{
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal)
    {
        $company = $request->user()->company;
        # get the authenticated organisation
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $filter = $request->input('filter', 'all');
        # the filter to apply on the request -- possible values: all (default), installed_only, without_installed
        $category = null;
        # get the category
        if ($request->has('category_slug')) {
            $category = ApplicationCategory::where('slug', $request->input('category_slug'))->first();
            # set the category
        }
        $defaultInstallState = false;
        $installedAppIds = [];
        # our installed app ids
        $builder = Application::withCount(['installs'])
                                ->with(['categories'])
                                ->where('is_published', 1)
                                ->when($category, function ($query) use ($category) {
                                    return $query->whereIn('id', function ($query) use ($category) {
                                        $query->select('application_id')
                                            ->from('application_category')
                                            ->where('application_category_id', $category->id);
                                    });
                                });
        if (!empty($filter)) {
            # we have a filter set
            switch (strtolower($filter)) {
                case 'installed_only':
                    $defaultInstallState = true;
                    # set the default install state
                    $builder->whereIn('id', function ($query) use ($company) {
                       $query->select('application_id')
                                ->from('application_installs')
                                ->where('company_id', $company->id);
                    });
                    break;
                case 'without_installed':
                    $builder->whereNotIn('id', function ($query) use ($company) {
                        $query->select('application_id')
                                ->from('application_installs')
                                ->where('company_id', $company->id);
                    });
                    break;
                default:
                    $installedAppIds = $company->applicationInstalls()->pluck('id')->all();
                    # get the installed application ids
                    break;
            }
        }
        $paginator = $builder->oldest('name')->paginate($limit);
        # get the applications
        $resource = new Collection($paginator->getCollection(), new ApplicationTransformer($installedAppIds, $defaultInstallState), 'application');
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
    public function get(Request $request, Manager $fractal, string $id)
    {
        $application = Application::withCount(['installs'])
                                    ->with(['categories'])
                                    ->where('uuid', $id)
                                    ->firstOrFail();
        # try to get the application
        $company = $request->user()->company;
        # get the company
        $installed = $company->applicationInstalls()->where('application_id', $application->id)->count();
        # get the number of installed apps
        $resource = new Item($application, new ApplicationTransformer([], $installed > 0), 'application');
        # create the resource
        return response()->json($fractal->createData($resource));
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function install(Request $request, Manager $fractal, string $id)
    {
        $application = Application::where('uuid', $id)->firstOrFail();
        # try to get the application
        $company = $request->user()->company;
        # get the company
        $install = $company->applicationInstalls()->create([
            'application_id' => $application->id
        ]);
        # install the application
        if (empty($install)) {
            throw new \RuntimeException('Installation failed... Please try again.');
        }
        $resource = new Item($install, new ApplicationInstallTransformer(), 'application_install');
        return response()->json($fractal->createData($resource), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uninstallApplicationViaAppId(Request $request, Manager $fractal, string $id)
    {
        $request->request->set('using_app_id', 1);
        return $this->uninstallApplication($request, $fractal, $id);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uninstallApplication(Request $request, Manager $fractal, string $id)
    {
        $company = $request->user()->company;
        # get the company
        if ($request->has('using_app_id')) {
            $application = Application::where('uuid', $id)->first();
            if (empty($application)) {
                throw new RecordNotFoundException('Could find the application to be uninstalled.');
            }
            $install = $company->applicationInstalls()->where('application_id', $application->id)->firstOrFail();
        } else {
            $install = $company->applicationInstalls()->where('uuid', $id)->firstOrFail();
        }
        # get the installation
        if (!(clone $install)->delete()) {
            throw new DeletingFailedException('Could not uninstall the application. Please try again.');
        }
        $transformer = new ApplicationInstallTransformer();
        $transformer->setDefaultIncludes([])->setAvailableIncludes([]);
        # configure the transformer
        $resource = new Item($install, $transformer, 'application_install');
        return response()->json($fractal->createData($resource));
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInstallation(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'extra_json' => 'required|array'
        ]);
        # validate the request
        $company = $request->user()->company;
        # get the company
        $install = $company->applicationInstalls()->where('uuid', $id)->firstOrFail();
        # get the installation
        $install->extra_json = $request->input('extra_json', []);
        # set the data
        $install->saveOrFail();
        # save the changes
        $resource = new Item($install, new ApplicationInstallTransformer(), 'application_install');
        return response()->json($fractal->createData($resource));
    }
}