<?php

namespace App\Http\Controllers\ECommerce\Domains;


use App\Events\ECommerce\DomainDeleted;
use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Domain;
use App\Http\Controllers\ECommerce\Domains\DomainIssuances;
use App\Transformers\DomainIssuanceTransformer;
use App\Transformers\DomainTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Domains extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'domain' => 'domain',
        'hosting_box_id' => 'hosting_box_id',
        'configuration' => 'configuration_json'
    ];
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hostingCapacity(Request $request)
    {
        $id = $request->query('id');
        # get the specific box we're searching for
        $results = DB::table('domains')
                        ->select(DB::raw('count(*) as domains_count, hosting_box_id'))
                        ->whereNotNull('hosting_box_id')
                        ->when($id, function ($query) use ($id) {
                            return $query->where('hosting_box_id', $id);
                        })
                        ->groupBy('hosting_box_id')
                        ->get();
        return response()->json(['data' => $results->all()]);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company();
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($search)) {
            # no search parameter
            $paginator = $company->domains()->with(['issuances'])
                                            ->oldest('domain')
                                            ->paginate($limit);
        } else {
            # searching for something
            $paginator = Domain::search($search)
                                            ->where('domainable_id', $company->id)
                                            ->paginate($limit);
        }
        # get the records
        $transformer = new DomainTransformer();
        $transformer->setDefaultIncludes(['issuances', 'owner']);
        $resource = new Collection($paginator->getCollection(), $transformer, 'domain');
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
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Manager $fractal)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'domain' => 'required|max:80',
            'hosting_box_id' => 'nullable|string|max:50',
            'configuration' => 'nullable|array'
        ]);
        # validate the request
        $domain = $company->domains()->create([
            'domain' => $request->domain,
            'hosting_box_id' => $request->input('hosting_box_id', null),
            'configuration_json' => $request->input('configuration', [])
        ]);
        # create the domain
        $resource = new Item($domain, new DomainTransformer(), 'domain');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the authenticated company
        $domain = $company->domains()->where('uuid', $id)->firstOrFail();
        # get the domain
        if (!(clone $domain)->delete()) {
            throw new DeletingFailedException('Could not delete the domain from the account.');
        }
        $transformer = (new DomainTransformer())->setAvailableIncludes([])->setDefaultIncludes([]);
        # set the transformer
        $resource = new Item($domain, $transformer, 'domain');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function single(Request $request, Manager $fractal, string $id)
    {
        $domain = static::resolve($request, $id);
        # try to get the domain
        $company = $this->company();
        # get the authenticated company
        $model = $domain->domainable;
        # we get the owner
        if ($model instanceof Company && $model->id !== $company->id) {
            # we check ownership
            throw new RecordNotFoundException('We found the domain, but it does not belong to you.');
        }
        $transformer = new DomainTransformer();
        $transformer->setDefaultIncludes(['issuances', 'owner']);
        # set the transformer
        $resource = new Item($domain, $transformer, 'domain');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'domain' => 'nullable|max:80',
            'hosting_box_id' => 'nullable|string|max:50',
            'configuration' => 'nullable|array'
        ]);
        # validate the request
        $domain = $company->domains()->where('uuid', $id)->firstOrFail();
        # get the domain
        $this->updateModelAttributes($domain, $request);
        # update the attribute values
        $domain->saveOrFail();
        # commit the changes
        $resource = new Item($domain, new DomainTransformer(), 'domain');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * Resolves the domain, or issuance.
     *
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resolver(Request $request, Manager $fractal)
    {
        $id = $request->query('id');

        dd($id);
        # the ID to be resolved
        if (empty($id)) {
            throw new \UnexpectedValueException('You did not pass the ID of the [sub]domain to be resolved.');
        }
        $resolver = strtolower($request->query('resolver', 'issuance'));
        # get the resolver
        switch ($resolver) {
            case 'domain':
                $model = static::resolve($request, $id);
                $resource = new Item($model, new DomainTransformer(), 'domain');
                break;
            default:
                $model = DomainIssuances::resolve($request, $id);
                $resource = new Item($model, new DomainIssuanceTransformer(), 'issuance');
        }
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * Resolve the domain model.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function resolve(Request $request, string $id)
    {
        return Domain::with('domainable')
                        ->where(function ($query) use ($id) {
                            $query->where('uuid', $id)
                                    ->orWhere('domain', $id);
                        })
                        ->firstOrFail();
        # get the domain
    }
}