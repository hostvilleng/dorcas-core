<?php

namespace App\Http\Controllers\Crm\Customer;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\ContactFieldTransformer;
use App\Transformers\CustomerTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CustomerContacts extends Controller
{
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
        $this->validate($request, [
            'field' => 'required_without:fields|string',
            'fields' => 'required_without:field|array'
        ]);
        # validate the request
        $customer = $company->customers()->with(['contacts'])->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $uuids = $request->has('fields') ? $request->input('fields') : [$request->input('field')];
        # array of UUIDs to process
        $filtered = $customer->contacts->whereNotIn('uuid', $uuids);
        # filter the list, and remove the requested field
        $syncData = $filtered->mapWithKeys(function ($contact) {
            return [$contact->id => ['value' => $contact->pivot->value]];
        })->all();
        # the data we'll be syncing to the database
        $customer->contacts()->sync($syncData);
        # add the contact information
        $resource = new Collection($filtered, new ContactFieldTransformer(), 'contact');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
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
        $customer = $company->customers()->with(['contacts'])->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $resource = new Collection($customer->contacts, new ContactFieldTransformer(), 'contact');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }

    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request, Manager $fractal, string $id)
    {
        $company = $this->company($request);
        # retrieve the company
        $this->validate($request, [
            'field' => 'required_without:fields|string',
            'value' => 'required_with:field|string|max:100',
            'fields' => 'nullable|array',
            'fields.*.id' => 'required|string',
            'fields.*.value' => 'required|string|max:100'

        ]);
        # validate the request
        $customer = $company->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $customerContacts = (clone $customer)->contacts;
        # the current contact fields for this customer
        $syncContacts = [];
        # the fields to sync
        if (!empty($customerContacts)) {
            # we have contacts
            foreach ($customerContacts as $contactField) {
                $syncContacts[$contactField->uuid] = ['value' => $contactField->pivot->value, 'id' => $contactField->id];
            }
        }
        $fields = $request->has('fields') ? $request->input('fields') : [['id' => $request->input('field'), 'value' => $request->input('value')]];
        # get the fields to be updated/created
        foreach ($fields as $id => $edit) {
            if (!array_key_exists($edit['id'], $syncContacts)) {
                continue;
            }
            $syncContacts[$edit['id']]['value'] = $edit['value'];
            # update the value
            unset($fields[$id]);
            # remove this index since it has been attended to
        }
        $fields = collect($fields);
        $contactFields = $company->contactFields()->whereIn('uuid', $fields->pluck('id'))->get();
        # try to get the contact fields
        foreach ($contactFields as $contactField) {
            $edit = $fields->where('id', $contactField->uuid)->first();
            if (empty($edit)) {
                continue;
            }
            $syncContacts[$contactField->uuid] = ['value' => $edit['value'], 'id' => $contactField->id];
            # add the field
        }
        $contacts = collect($syncContacts)->mapWithKeys(function ($body) {
            return [$body['id'] => ['value' => $body['value']]];
        });
        $customer->contacts()->sync($contacts);
        # add the contact information.
        $resource = new Item($customer, new CustomerTransformer(), 'customer');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}