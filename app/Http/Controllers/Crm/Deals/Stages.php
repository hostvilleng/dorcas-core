<?php

namespace App\Http\Controllers\Crm\Deals;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\DealStageTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Stages extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'name' => 'name',
        'value_amount' => 'value_amount',
        'note' => 'note',
        'entered_at' => 'entered_at',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:80',
            'value_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
            'entered_at' => 'nullable|date_format:"Y-m-d H:i:s"'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $deal = $company->deals()->where('uuid', $id)->first();
        # try to get the deal
        if (empty($deal)) {
            throw new RecordNotFoundException('Could not find the deal to create the stage for.');
        }
        $stage = $deal->stages()->create([
            'name' => $request->input('name'),
            'value_amount' => $request->input('value_amount', 0.00),
            'note' => $request->input('note'),
            'entered_at' => $request->input('entered_at'),
        ]);
        # create the model
        $transformer = (new DealStageTransformer())->setDefaultIncludes(['deal']);
        $resource = new Item($stage, $transformer, 'deal_stage');
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
        $this->validate($request, [
            'id' => 'required|string'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $deal = $company->deals()->where('uuid', $id)->first();
        # try to get the deal
        if (empty($deal)) {
            throw new RecordNotFoundException('Could not find the deal to create the stage for.');
        }
        $stage = $deal->stages()->where('uuid', $request->input('id'))->firstOrFail();
        # get the stage
        if (!(clone $stage)->delete()) {
            throw new DeletingFailedException('Failed while deleting the deal stage');
        }
        $transformer = (new DealStageTransformer())->setDefaultIncludes([])->setAvailableIncludes([]);
        $resource = new Item($stage, $transformer, 'deal_stage');
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
    public function update(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'id' => 'required|string',
            'name' => 'nullable|string|max:80',
            'value_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
            'entered_at' => 'nullable|date_format:"Y-m-d H:i:s"'
        ]);
        # validate the request
        $company = $this->company();
        # get the company
        $deal = $company->deals()->where('uuid', $id)->firstOrFail();
        # try to get the deal
        $stage = $deal->stages()->where('uuid', $request->input('id'))->firstOrFail();
        # get the stage
        $this->updateModelAttributes($stage, $request);
        # update the attributes
        $stage->saveOrFail();
        # save the changes
        $resource = new Item($stage, new DealStageTransformer(), 'deal_stage');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}