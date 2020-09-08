<?php

namespace App\Http\Controllers\Crm\Customer;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Transformers\ContactFieldTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class ContactField extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'name' => 'name'
    ];

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
        $contactField = $company->contactFields()->where('uuid', $id)->firstOrFail();
        # try to get the contact field
        if (!(clone $contactField)->delete()) {
            throw new DeletingFailedException('Failed while deleting the contact field');
        }
        $resource = new Item($contactField, new ContactFieldTransformer(), 'contact_field');
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
    public function index(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $contactField = $company->contactFields()->where('uuid', $id)->firstOrFail();
        # try to get the contact field
        $resource = new Item($contactField, new ContactFieldTransformer(), 'contact_field');
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
            'name' => 'required|max:40'
        ]);
        # validate the request
        $contactField = $company->contactFields()->where('uuid', $id)->firstOrFail();
        # try to get the contact field
        $this->updateModelAttributes($contactField, $request);
        # update the attributes
        $contactField->saveOrFail();
        # save the changes
        $resource = new Item($contactField, new ContactFieldTransformer(), 'contact_field');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}