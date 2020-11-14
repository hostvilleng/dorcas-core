<?php

namespace App\Http\Controllers\Integrations;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Transformers\IntegrationTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Integrations extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'type' => 'type',
        'name' => 'name',
        'configuration' => 'configuration',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal)
    {
        $type = $request->query('type');
        # filter the integrations by type
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company();
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->integrations()->when($type, function ($query) use ($type) {
                                                return $query->where('type', $type);
                                            })
                                            ->oldest('type')
                                            ->oldest('name')
                                            ->paginate($limit);
        # get the integrations
        $resource = new Collection($paginator->getCollection(), new IntegrationTransformer(), 'integration');
        # create the resource
        if (!empty($type)) {
            $pagingAppends['type'] = $type;
            # append the search term to the paginator
            $resource->setMetaValue('type', $type);
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, Manager $fractal)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'type' => 'required|max:30',
            'name' => 'required|max:50',
            'configuration' => 'nullable|array'
        ]);
        # validate the request
        $integration = $company->integrations()->create([
            'type' => $request->type,
            'name' => $request->name,
            'configuration' => $request->has('configuration') ? $request->configuration : []
        ]);
        # create the integration
        $resource = new Item($integration, new IntegrationTransformer(), 'integration');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $integration = $company->integrations()->where('uuid', $id)->firstOrFail();
        # try to get the integration
        if (!(clone $integration)->delete()) {
            throw new DeletingFailedException('Failed while deleting the integration');
        }
        $resource = new Item($integration, new IntegrationTransformer(), 'integration');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     *
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $integration = $company->integrations()->where('uuid', $id)->firstOrFail();
        # try to get the integration
        $resource = new Item($integration, new IntegrationTransformer(), 'integration');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'type' => 'nullable|max:30',
            'name' => 'nullable|max:50',
            'configuration' => 'nullable|array'
        ]);
        # validate the request
        $integration = $company->integrations()->where('uuid', $id)->firstOrFail();
        # try to get the integration
        $this->updateModelAttributes($integration, $request);
        # update the attributes
        $integration->saveOrFail();
        # save the changes
        $resource = new Item($integration, new IntegrationTransformer(), 'integration');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}