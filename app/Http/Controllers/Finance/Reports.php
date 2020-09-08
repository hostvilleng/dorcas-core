<?php

namespace App\Http\Controllers\Finance;


use App\Http\Controllers\Controller;
use App\Transformers\AccountingReportConfigurationTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Reports extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'report_name' => 'report_name'
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function configuredReports(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->reportConfigurations()->when($search, function ($query) use ($search) {
                                                        return $query->where('report_name', 'like', '%'.$search.'%');
                                                    })
                                                    ->oldest('report_name')
                                                    ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new AccountingReportConfigurationTransformer(), 'report_configuration');
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
     * @throws \Throwable
     */
    public function configure(Request $request, Manager $fractal)
    {
        $company = $this->company($request);
        # get the company
        $this->validate($request, [
            'report_name' => 'required|string',
            'accounts' => 'required|array',
            'accounts.*' => 'required|string',
        ]);
        # validate the request
        $configuration = $company->reportConfigurations()->firstOrNew(['report_name' => $request->report_name]);
        # get the configuration
        if (empty($configuration->configuration)) {
            $config = ['accounts' => []];
        } else {
            $config = $configuration->configuration;
        }
        $config['accounts'] = $company->accountingAccounts()->whereIn('uuid', $request->accounts)->pluck('id')->all();
        # get the selected account ids
        $configuration->configuration = $config;
        # set the accounts
        $configuration->saveOrFail();
        # save the changes
        $resource = new Item($configuration, new AccountingReportConfigurationTransformer(), 'report_configuration');
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
    public function reportConfiguration(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $configuration = $company->reportConfigurations()->where('uuid', $id)->firstOrFail();
        # get the configuration
        $resource = new Item($configuration, new AccountingReportConfigurationTransformer(), 'report_configuration');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
    public function updateReportConfiguration(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # get the company
        $this->validate($request, [
            'report_name' => 'nullable|string',
            'accounts' => 'nullable|array',
            'accounts.*' => 'required|string',
        ]);
        # validate the request
        $configuration = $company->reportConfigurations()->where('uuid', $id)->firstOrFail();
        # get the configuration
        if (empty($configuration->configuration)) {
            $config = ['accounts' => []];
        } else {
            $config = $configuration->configuration;
        }
        if ($request->has('accounts')) {
            $config['accounts'] = $company->accountingAccounts()->whereIn('uuid', $request->accounts)->pluck('id')->all();
            # get the selected account ids
            $configuration->configuration = $config;
            # set the accounts
        }
        $this->updateModelAttributes($configuration, $request);
        # update the attributes
        $configuration->saveOrFail();
        # save the changes
        $resource = new Item($configuration, new AccountingReportConfigurationTransformer(), 'report_configuration');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}