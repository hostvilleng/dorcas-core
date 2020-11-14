<?php

namespace App\Http\Controllers\Finance;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\AccountingAccountTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Accounts extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'display_name' => 'display_name',
        'entry_type' => 'entry_type',
        'is_visible' => 'is_visible'
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        $paginator = $company->accountingAccounts()->when($search, function ($query) use ($search) {
                                                        return $query->where('name', 'like', '%'.$search.'%')
                                                                        ->orWhere('display_name', 'like', '%'.$search.'%');
                                                    })
                                                    ->oldest('display_name')
                                                    ->oldest('name')
                                                    ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new AccountingAccountTransformer(), 'account');
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
        $company = $this->company($request);
        # get the company
        $this->validate($request, [
            'name' => 'required|string',
            'display_name' => 'nullable|string',
            'parent_account_id' => 'nullable|string',
            'entry_type' => 'required|in:credit,debit'
        ]);
        # validate the request
        $account = null;
        if ($request->has('parent_account_id')) {
            $baseAccount = $company->accountingAccounts()->where('uuid', $request->parent_account_id)->first();
            if (empty($baseAccount)) {
                throw new RecordNotFoundException('Could not find the selected base account.');
            }
            $account = $baseAccount->subAccounts()->create([
                'company_id' => $company->id,
                'entry_type' => $request->entry_type,
                'name' => $request->name,
                'display_name' => $request->input('display_name', null),
                'is_visible' => 1
            ]);
            # create the account from the parent account
        } else {
            $account = $company->accountingAccounts()->create([
                'entry_type' => $request->entry_type,
                'name' => $request->name,
                'display_name' => $request->input('display_name', null),
                'is_visible' => 1
            ]);
        }
        $resource = new Item($account, new AccountingAccountTransformer(), 'account');
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
        $company = $this->company($request);
        # get the company
        $account = $company->accountingAccounts()->where('uuid', $id)->firstOrFail();
        # get the account
        if (!(clone $account)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified account, and related entries. Please try again.'
            );
        }
        $transformer = new AccountingAccountTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($account, $transformer, 'account');
        return response()->json($fractal->createData($resource)->toArray(), 200);
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
        $company = $this->company($request);
        # get the company
        $account = $company->accountingAccounts()->where('uuid', $id)->firstOrFail();
        # get the account
        $resource = new Item($account, new AccountingAccountTransformer(), 'account');
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
    public function update(Request $request, Manager $fractal, string $id)
    {
        $this->validate($request, [
            'display_name' => 'nullable|string',
            'parent_account_id' => 'nullable|string',
            'entry_type' => 'nullable|in:credit,debit',
            'is_visible' => 'nullable|numeric|in:0,1'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $account = $company->accountingAccounts()->where('uuid', $id)->firstOrFail();
        # get the account
        if ($request->has('parent_account_id') && $request->parent_account_id === 'none') {
            # set it to null
            $account->parent_account_id = null;
            $request->request->remove('parent_account_id');
        }
        $this->updateModelAttributes($account, $request);
        # update the attributes
        $account->saveOrFail();
        # save the changes
        $resource = new Item($account, new AccountingAccountTransformer(), 'account');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}