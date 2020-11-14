<?php

namespace App\Http\Controllers\Crm\Customer;


use App\Exceptions\DeletingFailedException;
use App\Http\Controllers\Controller;
use App\Models\CustomerNote;
use App\Transformers\CustomerNoteTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Notes extends Controller
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
        $this->validate($request, [
            'id' => 'required|string'
        ]);
        # validate the request
        $customer = $this->company()->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $note = $customer->notes()->where('uuid', $request->input('id'))->firstOrFail();
        # get the note
        if (!(clone $note)->delete()) {
            throw new DeletingFailedException('Could not delete the note.');
        }
        $resource = new Item($note, new CustomerNoteTransformer(), 'note');
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
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $customer = $this->company()->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (!empty($search)) {
            # there's a search term in the URL
            $paginator = CustomerNote::search($search)->where('customer_id', $customer->id)
                                                    ->orderBy('created_at', 'desc')
                                                    ->paginate($limit);
            # get the notes
        } else {
            $paginator = $customer->notes()->latest()->paginate($limit);
            # get the customers
        }
        $resource = new Collection($paginator->getCollection(), new CustomerNoteTransformer(), 'note');
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
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'note' => 'required|string'
        ]);
        # validate the request
        $customer = $this->company()->customers()->where('uuid', $id)->firstOrFail();
        # try to get the customer
        $note = $customer->notes()->create(['message' => $request->input('note')]);
        # create the note
        $resource = new Item($note, new CustomerNoteTransformer(), 'note');
        return response()->json($fractal->createData($resource)->toArray(), 201);

    }
}