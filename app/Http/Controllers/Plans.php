<?php

namespace App\Http\Controllers;


use App\Exceptions\DeletingFailedException;
use App\Models\Plan;
use App\Transformers\PlanTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Plans extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'name' => 'name',
        'price_monthly' => 'price_monthly',
        'price_yearly' => 'price_yearly'
    ];

    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Manager $fractal)
    {
        $plans = Plan::oldest('name')->get();
        # get all the plans
        $resource = new Collection($plans, new PlanTransformer(), 'plan');
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
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $plan = Plan::where('uuid', $id)->firstOrFail();
        # get the plan
        $deleteMethod = $plan->companies()->count() > 0 ? 'delete' : 'forceDelete';
        # we decide how to delete the plan
        if (!(clone $plan)->{$deleteMethod}()) {
            # the delete failed
            throw new DeletingFailedException('Failed while deleting the pricing plan');
        }
        $resource = new Item($plan, new PlanTransformer(), 'plan');
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
    public function view(Request $request, Manager $fractal, string $id)
    {
        $plan = Plan::where('uuid', $id)->firstOrFail();
        # get the plan
        $resource = new Item($plan, new PlanTransformer(), 'plan');
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
        $this->validate($request, [
            'name' => 'nullable|max:80',
            'price_monthly' => 'nullable|numeric',
            'price_yearly' => 'nullable|numeric'
        ]);
        # validate the request
        $plan = Plan::where('uuid', $id)->firstOrFail();
        # get the plan
        $this->updateModelAttributes($plan, $request);
        # update the attributes
        $plan->saveOrFail();
        # save the changes
        $resource = new Item($plan, new PlanTransformer(), 'plan');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}