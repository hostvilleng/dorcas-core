<?php

namespace App\Http\Controllers\Directory;


use App\Events\Professional\NewServiceRequest;
use App\Events\Professional\ServiceRequestStatusChanged;
use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\VendorService;
use App\Transformers\ServiceRequestTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ServiceRequests extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'message' => 'message',
        'is_read' => 'is_read',
        'status' => 'status'
    ];
    
    /**
     * @param Request $request
     *
     * @return Builder
     *
     */
    private function getBuilder(Request $request): Builder
    {
        $mode = $request->input('mode', 'professional');
        # get the mode
        $user = $request->user();
        # get the authenticated user
        return ServiceRequest::whereIn('service_id', function ($query) use ($user, $mode) {
            $query->select('id')->from('professional_services')->where('user_id', $user->id)->where('type', $mode);
        });
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, Manager $fractal)
    {
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $companyId = $request->query('company_id');
        # get the category id, if specified
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $this->getBuilder($request)->with(['company', 'service'])
                                                        ->when($companyId, function ($query) use ($companyId) {
                                                            return $query->where('company_id', $companyId);
                                                        })
                                                        ->latest()
                                                        ->paginate($limit);
        # get the model
        $resource = new Collection($paginator->getCollection(), new ServiceRequestTransformer(), 'service_request');
        # create the resource
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
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $companyId = $request->query('company_id', null);
        # get the requesting company, if specified
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $this->getBuilder($request)->with(['company', 'service'])
                                                ->where('service_id', $id)
                                                ->when($companyId, function ($query) use ($companyId) {
                                                    return $query->where('company_id', $companyId);
                                                })
                                                ->latest()
                                                ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new ServiceRequestTransformer(), 'service_request');
        # create the resource
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
    public function create(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:6144'
        ]);
        # validate the request
        $user = $request->user();
        # get the currently logged in user
        $service = ProfessionalService::with('categories', 'user')->where('uuid', $id)->first();
        if (empty($service)) {
            $service = VendorService::with('categories', 'user')->where('uuid', $id)->firstOrFail();
        }
        # get the service
        if ($request->has('attachment')) {
            # we're requesting a file upload
            $path = $request->file('attachment')->store('services/' . $service->uuid . '/requests');
        }
        $serviceRequest = $service->requests()->create([
            'company_id' => $user->company_id,
            'message' => $request->message,
            'attachment_url' => !empty($path) ? $path : null,
            'status' => 'pending'
        ]);
        # create the request
        event(new NewServiceRequest($serviceRequest));
        # trigger the event
        $resource = new Item($serviceRequest, new ServiceRequestTransformer(), 'service_request');
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
        $serviceRequest = ServiceRequest::with('service', 'company')->where('uuid', $id)->firstOrFail();
        # get the request
        if (!(clone $serviceRequest)->delete()) {
            throw new DeletingFailedException('Failed while deleting the service request. Please try again.');
        }
        $transformer = new ServiceRequestTransformer();
        $transformer->setDefaultIncludes(['company', 'service']);
        $resource = new Item($serviceRequest, $transformer, 'service_request');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function single(Request $request, Manager $fractal, string $id)
    {
        $serviceRequest = ServiceRequest::with('service', 'company')->where('uuid', $id)->firstOrFail();
        # get the request
        $serviceRequest->is_read = 1;
        $serviceRequest->save();
        # since it was specifically requested, we set it as read
        $resource = new Item($serviceRequest, new ServiceRequestTransformer(), 'service_request');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $this->validate($request, [
            'message' => 'nullable|string',
            'is_read' => 'nullable|numeric|in:0,1',
            'status' => 'nullable|string|in:accepted,pending,rejected'
        ]);
        # validate the request
        $serviceRequest = ServiceRequest::with('service', 'company')->where('uuid', $id)->firstOrFail();
        # get the request
        $triggerEvent = $serviceRequest->is_pending;
        # the initial state was the pending state
        if (!$serviceRequest->is_read) {
            $serviceRequest->is_read = 1;
        }
        $this->updateModelAttributes($serviceRequest, $request);
        # update the attributes
        if ($triggerEvent && !$serviceRequest->is_pending) {
            # former state was pending, but it has changed now
            Event::dispatch(new ServiceRequestStatusChanged($serviceRequest));
        }
        $serviceRequest->saveOrFail();
        # save the changes
        $resource = new Item($serviceRequest, new ServiceRequestTransformer(), 'service_request');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * List service requests from a company.
     *
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listRequests(Request $request, Manager $fractal)
    {
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $companyId = $request->query('company_id', null);
        # get the requesting company, if specified
        $serviceId = $request->query('service_id', null);
        # get the service id we're filtering for
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $vendor = null;
        if ($request->has('vendor_id')) {
            $vendorId = $request->query('vendor_id', null);
            # get the vendor id
            $vendor = User::where('uuid', $vendorId)->first();
        }
        $paginator = ServiceRequest::with(['service'])
                                    ->when($serviceId, function ($query) use ($serviceId) {
                                        return $query->whereIn('service_id', function ($query) use ($serviceId) {
                                            $query->select('id')->from('professional_services')->where('uuid', $serviceId);
                                        });
                                    })
                                    ->when($companyId, function ($query) use ($companyId) {
                                        return $query->whereIn('company_id', function ($query) use ($companyId) {
                                            $query->select('id')->from('companies')->where('uuid', $companyId);
                                        });
                                    })
                                    ->when($vendor, function ($query) use ($vendor) {
                                        return $query->whereIn('service_id', function ($query) use ($vendor) {
                                            $query->select('id')->from('professional_services')->where('user_id', $vendor->id);
                                        });
                                    })
                                    ->latest()
                                    ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new ServiceRequestTransformer(), 'service_request');
        # create the resource
        $paginator->appends($pagingAppends);
        # add the append terms
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray());
    }
}