<?php

namespace App\Dorcas\Common;


use App\Exceptions\DeletingFailedException;
use App\Transformers\BankAccountTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

trait SupportsBankAccounts
{
    /**
     * Checks that the trait has been properly setup.
     *
     * @param Request|null $request
     *
     * @return Model
     */
    protected function getReferenceModel(Request $request = null): ?Model
    {
        return $this->getModel($request);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBankAccounts(Request $request, Manager $fractal)
    {
        $reference = $this->getReferenceModel($request);
        # our reference model
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $reference->bankAccounts()->when($search, function ($query) use ($search) {
                                                    return $query->where('account_no', 'like', '%' . $search . '%')
                                                                    ->orWhere('account_name', 'like', '%' . $search . '%');
                                                })
                                                ->oldest('account_name')
                                                ->latest()
                                                ->paginate($limit);
        # read the data
        $resource = new Collection($paginator->getCollection(), new BankAccountTransformer(), 'bank_account');
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
     */
    public function createBankAccount(Request $request, Manager $fractal)
    {
        $reference = $this->getReferenceModel($request);
        # gets the reference model
        $this->validate($request, [
            'account_number' => 'required|string|max:30',
            'account_name' => 'nullable|string|max:80',
            'json_data' => 'nullable|array',
        ]);
        # validate the request
        if (!$request->has('account_name') && !property_exists($this, 'name')) {
            throw new \UnexpectedValueException(
                'A name attribute/property needs to be defined on the model to automatically use it as the account name.'
            );
        }
        $account = $reference->bankAccounts()->create([
            'account_number' => $request->input('account_number'),
            'account_name' => $request->input('account_name', $reference->name),
            'json_data' => $request->input('json_data', [])
        ]);
        # create the account
        $resource = new Item($account, new BankAccountTransformer(), 'bank_account');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBankAccount(Request $request, Manager $fractal, string $id)
    {
        $reference = $this->getReferenceModel($request);
        # get the reference model
        $account = $reference->bankAccounts()->where('uuid', $id)->firstOrFail();
        # get the model
        if (!(clone $account)->delete()) {
            throw new DeletingFailedException('Could not delete the bank account. Please try again later.');
        }
        $resource = new Item($account, new BankAccountTransformer(), 'bank_account');
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
    public function singleBankAccount(Request $request, Manager $fractal, string $id)
    {
        $reference = $this->getReferenceModel($request);
        # get the reference model
        $account = $reference->bankAccounts()->where('uuid', $id)->firstOrFail();
        # get the model
        $resource = new Item($account, new BankAccountTransformer(), 'bank_account');
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
    public function updateBankAccount(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'account_number' => 'nullable|string|max:30',
            'account_name' => 'nullable|string|max:80',
            'json_data' => 'nullable|array',
        ]);
        # validate the request
        $reference = $this->getReferenceModel($request);
        # gets the reference model
        $account = $reference->bankAccounts()->where('uuid', $id)->firstOrFail();
        # get the model
        if ($request->has('account_number')) {
            $account->account_number = $request->account_number;
        }
        if ($request->has('account_name')) {
            $account->account_name = $request->account_name;
        }
        if ($request->has('json_data')) {
            $account->json_data = $request->input('json_data');
        }
        $account->saveOrFail();
        # save the changes
        $resource = new Item($account, new BankAccountTransformer(), 'bank_account');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }
}