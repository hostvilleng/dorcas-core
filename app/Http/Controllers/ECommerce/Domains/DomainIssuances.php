<?php

namespace App\Http\Controllers\ECommerce\Domains;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Domain;
use App\Models\DomainIssuance;
use App\Transformers\DomainIssuanceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class DomainIssuances extends Controller
{
    /**
     * List of reserved sub-domains.
     *
     * @var array
     */
    protected $reservedSubDomains = [
        'developers',
        'static',
        'cdn',
        'hostville',
        'tabitha',
        'bookingbase',
        'staging-api'
    ];
    
    /**
     * @param string      $prefix
     * @param Domain|null $domain
     *
     * @return bool
     */
    protected function isSubDomainAvailable(string $prefix, Domain $domain = null): bool
    {
        $count = DomainIssuance::where('prefix', $prefix)
                                ->when($domain, function ($query) use ($domain) {
                                    return $query->where('domain_id', $domain->id);
                                }, function ($query) {
                                    return $query->whereNull('domain_id');
                                })
                                ->count();
        # how many are there
        if (empty($domain)) {
            # since we're checking on our dorcas.ng / dorcas.io domains
            $count += in_array(strtolower($prefix), $this->reservedSubDomains) ? 1 : 0;
        }
        return $count === 0;
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $prefix = $request->query('id');
        # we search for the prefix availability
        if (empty($prefix)) {
            throw new \UnexpectedValueException('You need to type a name for the sub-domain to be searched!');
        }
        $domainId = $request->query('domain_id');
        # get the domain id to be searched against
        $domain = !empty($domainId) ? Domain::where('uuid', $domainId)->first() : null;
        # get the domain, if any
        $response = ['data' => ['is_available' => $this->isSubDomainAvailable($prefix, $domain)]];
        return response()->json($response);
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
        $paginator = $company->domainIssuances()->when($search, function ($query) use ($search) {
                                                    return $query->where('prefix', 'like', '%'.$search.'%');
                                                })
                                                ->oldest('prefix')
                                                ->paginate($limit);
        # get the records
        $resource = new Collection($paginator->getCollection(), new DomainIssuanceTransformer(), 'domain_issuance');
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
            'prefix' => 'required|max:80',
            'domain_id' => 'nullable'
        ]);
        # validate the request
        $domain = null;
        if ($request->has('domain_id')) {
            $domain = $company->domains()->where('uuid', $request->domain_id)->first();
            # try to get the domain
            if (empty($domain)) {
                throw new RecordNotFoundException('Could not find the domain with the provided id.');
            }
        }
        if (!$this->isSubDomainAvailable($request->input('prefix'), $domain)) {
            throw new \UnexpectedValueException('The requested sub-domain is not available.');
        }
        $field = $company->domainIssuances()->create([
            'prefix' => $request->input('prefix'),
            'domain_id' => !empty($domain) ? $domain->id : null
        ]);
        # create the model
        $resource = new Item($field, new DomainIssuanceTransformer(), 'domain_issuance');
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
        $issuance = $this->company()->domainIssuances()->where('uuid', $id)->firstOrFail();
        # try to get the issuance
        if (!(clone $issuance)->delete()) {
            throw new DeletingFailedException('Failed while releasing the subdomain.');
        }
        $resource = new Item($issuance, new DomainIssuanceTransformer(), 'issuance');
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
        $issuance = static::resolve($request, $id);
        # try to get the issuance
        $company = $this->company();
        # get the company
        $model = $issuance->domainable;
        # we get the owner
        if ($model instanceof Company && $model->id !== $company->id) {
            # we check ownership
            throw new RecordNotFoundException('We found the subdomain, but it does not belong to you.');
        }
        $resource = new Item($issuance, new DomainIssuanceTransformer(), 'issuance');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    /**
     * Resolves the domain issuance.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return mixed
     */
    public static function resolve(Request $request, string $id)
    {
        //dd("hello");
        $domain = null;
        # the domain model
        if ($request->has('domain_id')) {
            # a domain id is present in the request
            $domainId = $request->domain_id;
            $domain = Domain::where(function ($query) use ($domainId) {
                                $query->where('uuid', $domainId)
                                        ->orWhere('domain', $domainId);
                            })
                            ->first();
            # get the domain
        }
        return DomainIssuance::with('domainable')
                                ->where(function ($query) use ($id) {
                                    $query->where('uuid', $id)
                                            ->orWhere('prefix', $id);
                                })
                                ->when($domain, function ($query) use ($domain) {
                                    return $query->where('domain_id', $domain->id);
                                })
                                ->firstOrFail();
        # try to get the issuance
    }
}