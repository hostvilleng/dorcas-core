<?php

namespace App\Http\Controllers;


use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Transformers\OrderTransformer;
use App\Transformers\ProductTransformer;
use App\Transformers\ServiceRequestTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use League\Fractal\Manager;

class Statistics extends Controller
{
    const METRICS = [
        'company' => [

        ],
        'customers' => [
            'contact_fields_created', 'contact_fields_created_count',
            'groups_created', 'groups_created_count', 'groups_updated', 'groups_updated_count',
            'customers', 'customers_count'
        ],
        'products' => [
            'products_created', 'products_created_count', 'products_updated', 'products_updated_count',
            'sales', 'sales_total'
        ],
        'professional' => ['requests', 'requests_count', 'requests_accepted', 'requests_pending', 'requests_rejected'],
    ];

    public function read(Request $request)
    {
        $allowedResources = implode(',', array_keys(static::METRICS));
        # the allowed resources
        $this->validate($request, [
            'metrics' => 'required|array',
            'metrics.*.resource' => 'required|string|in:'.$allowedResources,
            'metrics.*.metrics' => 'required|array|min:1',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d'
        ], ['id.required' => 'You need to tell us the resource id you want to read stats for']);
        # validate the request
        $from = static::processDate($request->input('from_date')) ?: Carbon::now()->subDay();
        $to = static::processDate($request->input('to_date')) ?: Carbon::now();
        # set the dates
        if ($from->greaterThanOrEqualTo($to)) {
            throw new \UnexpectedValueException('The from date must be less than the to date value');
        }
        $metrics = $request->input('metrics');
        # get the available metrics for this resource
        $metricsData = [];
        # our metrics data
        foreach ($metrics as $metric) {
            $resource = strtolower($metric['resource']);
            $resourceMetrics = $metric['metrics'];
            # get the resource, and the required metrics
            $method = 'process' . title_case($resource) . 'Stats';
            $metricsData[$resource] = $this->{$method}($request, $resourceMetrics, $from, $to);
            # get the metrics data
        }
        return response()->json(['data' => $metricsData, 'metrics' => count($metricsData)], 200);
    }

    /**
     * @param Request $request
     * @param array   $metrics
     * @param Carbon  $from
     * @param Carbon  $to
     *
     * @return null|array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function processProductsStats(Request $request, array $metrics, Carbon $from, Carbon $to)
    {
        $fractal = app(Manager::class);
        # get the manager
        $company = $this->company($request, true, false);
        # get the currently authenticated company
        $availableMetrics = static::METRICS['products'] ?? [];
        # get the available metrics for this resource
        $processableMetrics = collect($metrics)->filter(function ($metric) use ($availableMetrics) {
            return in_array($metric, $availableMetrics);
        })->all();
        # get the processable ones
        if (empty($processableMetrics)) {
            return null;
        }
        $from = $from->startOfDay();
        $to = $to->endOfDay();
        $builder = $company->products()->where(function ($query) use ($from, $to) {
                                            $query->where(function ($query) use ($from, $to) {
                                                        $query->where('created_at', '>=', $from->format('Y-m-d H:i:s'))
                                                                ->where('created_at', '<=', $to->format('Y-m-d H:i:s'));
                                                    })
                                                    ->orWhere(function ($query) use ($from, $to) {
                                                        $query->where('updated_at', '>=', $from->format('Y-m-d H:i:s'))
                                                                ->where('updated_at', '<=', $to->format('Y-m-d H:i:s'));
                                                    });
                                            })
                                            ->latest();
        $products = $builder->get();
        # get the records
        $payload = [];
        # the payload
        $orders = null;
        # the orders for the time period
        if (in_array('sales', $processableMetrics) || in_array('sales_total', $processableMetrics)) {
            $orders = $company->orders()->where(function ($query) use ($from, $to) {
                                            $query->where('created_at', '>=', $from->format('Y-m-d H:i:s'))
                                                    ->orWhere('created_at', '<=', $to->format('Y-m-d H:i:s'));
                                        })
                                        ->latest()
                                        ->get();
        }
        foreach ($processableMetrics as $metric) {
            $transformer = null;
            $resourceName = null;
            $payload[$metric] = [];
            # set the entry
            if ($metric === 'products_created') {
                $transformer = new ProductTransformer();
                $transformer->setDefaultIncludes([]);
                $resourceName = 'product';
                $payload[$metric] = static::processCollectionItemsByDate($products, 'created_at', $from, $to);

            } elseif ($metric === 'products_created_count') {
                $payload[$metric] = static::processCollectionCountByDate($products, 'created_at', $from, $to);

            } elseif ($metric === 'products_updated') {
                $transformer = new ProductTransformer();
                $transformer->setDefaultIncludes([]);
                $resourceName = 'product';
                $payload[$metric] = static::processCollectionItemsByDate($products, 'updated_at', $from, $to);

            } elseif ($metric === 'products_updated_count') {
                $payload[$metric] = static::processCollectionCountByDate($products, 'updated_at', $from, $to);

            } elseif ($metric === 'sales' && !empty($orders)) {
                $transformer = new OrderTransformer();
                $transformer->setDefaultIncludes([]);
                $resourceName = 'order';
                $payload[$metric] = static::processCollectionItemsByDate($orders, 'created_at', $from, $to);

            } elseif ($metric === 'sales_total' && !empty($orders)) {
                $data = $payload['sales'] ?? static::processCollectionItemsByDate($orders, 'created_at', $from, $to);
                # get the data
                $payload[$metric] = collect($data)->mapWithKeys(function ($items, $key) {
                    $items = collect($items);
                    $currencies = $items->unique('currency')->pluck('currency')->all();
                    $currencySales = [];
                    $amountKey = !empty($payload['sales']) ? 'amount.raw' : 'amount';
                    foreach ($currencies as $currency) {
                        $ordersForCurrency = $items->where('currency', $currency);
                        $currencySales[$currency] = [
                            'count' => $ordersForCurrency->count(),
                            'total' => $ordersForCurrency->pluck($amountKey)->sum()
                        ];
                    }
                    return [$key => $currencySales];

                })->all();
            }
            if (!empty($transformer)) {
                $payload[$metric] = collect($payload[$metric])->mapWithKeys(function ($orders, $key) use ($fractal, $transformer, $resourceName) {
                    if (empty($orders)) {
                        return [$key => []];
                    }
                    $collection = collect($orders);
                    $resource = new \League\Fractal\Resource\Collection($collection, $transformer, $resourceName);
                    return [$key => $fractal->createData($resource)->toArray()['data']];
                })->all();
            }
        }
        return $payload;
    }
    
    /**
     * @param Request $request
     * @param array   $metrics
     * @param Carbon  $from
     * @param Carbon  $to
     *
     * @return array|null
     */
    protected function processProfessionalStats(Request $request, array $metrics, Carbon $from, Carbon $to)
    {
        $fractal = app(Manager::class);
        # get the manager
        $user = $request->user();
        # get the currently authenticated user
        if (!$user->is_professional && !$user->is_vendor) {
            throw new \RuntimeException('A professional/vendor profile does not exist for this account yet.');
        }
        $availableMetrics = static::METRICS['professional'] ?? [];
        # get the available metrics for this resource
        $processableMetrics = collect($metrics)->filter(function ($metric) use ($availableMetrics) {
            return in_array($metric, $availableMetrics);
        })->all();
        # get the processable ones
        if (empty($processableMetrics)) {
            return null;
        }
        $from = $from->startOfDay();
        $to = $to->endOfDay();
        $builder = $user->professionalServiceRequests()->whereRaw('`professional_service_requests`.`created_at` >= ?', [$from->format('Y-m-d H:i:s')])
                                                        ->whereRaw('`professional_service_requests`.`created_at` <= ?', [$to->format('Y-m-d H:i:s')])
                                                        ->latest();
        $requests = $builder->get();
        # get the records
        $payload = [];
        # the payload
        foreach ($processableMetrics as $metric) {
            $transformer = null;
            $resourceName = null;
            $payload[$metric] = [];
            if ($metric === 'requests') {
                $transformer = new ServiceRequestTransformer();
                $transformer->setDefaultIncludes([]);
                $resourceName = 'service_request';
                $payload[$metric] = static::processCollectionItemsByDate($requests, 'created_at', $from, $to);
                
            } elseif ($metric === 'requests_count') {
                $payload[$metric] = static::processCollectionCountByDate($requests, 'created_at', $from, $to);
        
            } elseif (in_array($metric, ['requests_accepted', 'requests_pending', 'requests_rejected'])) {
                $transformer = new ServiceRequestTransformer();
                $transformer->setDefaultIncludes([]);
                $resourceName = 'service_request';
                $check = substr($metric, 9);
                $statusCheck = $requests->filter(function ($serviceRequest) use ($check) {
                    return $serviceRequest->{'is_' . $check};
                });
                $payload[$metric] = static::processCollectionItemsByDate($statusCheck, 'created_at', $from, $to);
                
            }
            if (!empty($transformer)) {
                # we'll be processing the data further
                $payload[$metric] = collect($payload[$metric])->mapWithKeys(function ($serviceRequest, $key) use ($fractal, $transformer, $resourceName) {
                    if (empty($serviceRequest)) {
                        return [$key => []];
                    }
                    $collection = collect($serviceRequest);
                    $resource = new \League\Fractal\Resource\Collection($collection, $transformer, $resourceName);
                    return [$key => $fractal->createData($resource)->toArray()['data']];
                })->all();
            }
        }
        return $payload;
    }

    /**
     * Sorts the items of the collection into groups by date.
     *
     * @param Collection $collection
     * @param string     $attribute
     * @param Carbon     $from
     * @param Carbon     $to
     * @param bool       $filterFirst   whether or not to filter by the attribute first
     *
     * @return array
     */
    public static function processCollectionItemsByDate(Collection $collection, string $attribute, Carbon $from, Carbon $to, bool $filterFirst = true): array
    {
        $start = clone $from;
        $data = [];
        $from = $from->startOfDay();
        $to = $to->endOfDay();
        while ($start->lessThanOrEqualTo($to)) {
            $key = $start->format('Y-m-d');
            $data[$key] = [];
            # get the data
            $start->addDay();
        }
        if ($filterFirst) {
            $collection = $collection->filter(function ($item) use ($from, $to, $attribute) {
                return $item->{$attribute}->greaterThanOrEqualTo($from) && $item->{$attribute}->lessThanOrEqualTo($to);
            });
        }
        foreach ($collection as $item) {
            $key = $item->{$attribute}->format('Y-m-d');
            # the key
            if (empty($data[$key])) {
                $data[$key] = [];
            }
            $data[$key][] = $item;
        }
        return $data;
    }

    /**
     * Processes item counts using the date.
     *
     * @param Collection $collection
     * @param string     $attribute
     * @param Carbon     $from
     * @param Carbon     $to
     *
     * @return array
     */
    public static function processCollectionCountByDate(Collection $collection, string $attribute, Carbon $from, Carbon $to): array
    {
        $start = clone $from;
        $data = [];
        while ($start->lessThanOrEqualTo($to)) {
            $key = $start->format('Y-m-d');
            $data[$key] = $collection->filter(function ($model) use ($start, $attribute) {
                return $model->{$attribute}->greaterThanOrEqualTo($start->startOfDay()) &&
                    $model->{$attribute}->lessThanOrEqualTo($start->endOfDay());
            })->count();
            # get the data
            $start->addDay();
        }
        return $data;
    }

    /**
     * Retrieves the resource to pull up metrics for.
     *
     * @param string $type
     * @param string $id
     *
     * @return null|Company|Product|User
     */
    public static function getResource(string $type, string $id)
    {
        $resource = null;
        switch (strtolower($type)) {
            case 'company':
                $resource = Company::where('uuid', $id)->firstOrFail();
                break;
            case 'user':
                $resource = User::where('uuid', $id)->firstOrFail();
                break;
        }
        return $resource;
    }

    /**
     * @param string|null $date
     * @param string      $format
     * @param Carbon|null $default
     *
     * @return null|Carbon
     */
    public static function processDate(string $date = null, string $format = 'Y-m-d', Carbon $default = null)
    {
        if (empty($date)) {
            return null;
        }
        return Carbon::createFromFormat($format, $date) ?: $default;
    }
}