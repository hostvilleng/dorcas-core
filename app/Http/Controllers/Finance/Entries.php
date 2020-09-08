<?php

namespace App\Http\Controllers\Finance;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\AccountingEntry;
use App\Transformers\AccountingEntryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Entries extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'currency' => 'currency',
        'amount' => 'amount',
        'memo' => 'memo',
        'source_type' => 'source_type',
        'source_info' => 'source_info'
    ];
    
    /**
     * @param Request     $request
     * @param Manager     $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(Request $request, Manager $fractal, string $id="")
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        $company = $this->company($request);
        # retrieve the company
        //$id = $request->input('id');
        # account we want to filter by
        $pagingAppends = ['limit' => $limit];
        # append values for the paginator
        if (empty($id)) {
            $builder = $company->accountingEntries();
        } else {
            $account = $company->accountingAccounts()->with('subAccounts')->where('uuid', $id)->first();
            if (empty($account)) {
                throw new RecordNotFoundException('Could not find the specified account to search for entries in.');
            }
            $ids = [];
            if (!empty($account->subAccounts)) {
                $ids = $account->subAccounts->pluck('id')->all();
            }
            $ids[] = $account->id;
            # the ids we're interested in
            $builder = AccountingEntry::whereIn('account_id', $ids);
        }
        $paginator = $builder->when($search, function ($query) use ($search) {
                                    return $query->where('currency', 'like', '%'.$search.'%')
                                                    ->orWhere('source_type', 'like', '%'.$search.'%')
                                                    ->orWhere('source_info', 'like', '%'.$search.'%');
                                })
                                ->latest()
                                ->paginate($limit);
        $resource = new Collection($paginator->getCollection(), new AccountingEntryTransformer(), 'entry');
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
        $this->validate($request, [
            'account' => 'required|string',
            'currency' => 'nullable|string|size:3',
            'amount' => 'required|numeric',
            'memo' => 'nullable|string|max:300',
            'source_type' => 'nullable|string',
            'source_info' => 'nullable|string|max:300',
            'created_at' => 'nullable|date_format:Y-m-d',
            'double_entry_type' => 'nullable|string',
            'double_entry_period' => 'nullable|in:future,present',
        ]);
        # validate the request
        $doubleEntryCompanion = [
            'credit' => ['future' => 'accounts receivable', 'present' => 'cash'],
            'debit' => ['future' => 'accounts payable', 'present' => 'cash']
        ];
        # the configuration for loading appropriate account
        $company = $this->company($request);
        # get the company
        $account = $company->accountingAccounts()->where('uuid', $request->account)->first();
        if (empty($account)) {
            throw new RecordNotFoundException('Could not find the specified account to add the entry in.');
        }
        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
        } else {
            $createdAt = Carbon::now();
        }
        $doubleEntryType = $request->input('double_entry_type');
        if (empty($doubleEntryType)) {
            # no double entry type specified
            $entry = $account->entries()->create([
                'entry_type' => $account->entry_type,
                'currency' => strtoupper($request->input('currency', 'NGN')),
                'amount' => $request->amount,
                'memo' => $request->input('memo', null),
                'source_type' => $request->input('source_type', 'manual'),
                'source_info' => $request->input('source_info', 'manual'),
                'created_at' => $createdAt->format('Y-m-d H:i:s')
            ]);
            # create the entry
        } else {
            # we have it specified
            $period = strtolower($request->input('double_entry_period', 'present'));
            # we assume everything is in the present
            $configuration = $doubleEntryCompanion[$doubleEntryType];
            # the configuration
            $oppositeEntryType = $doubleEntryType === 'debit' ? 'credit' : 'debit';
            # set the opposite entry type
            $oppositeEntryAccount = $company->accountingAccounts()->where('name', $configuration[$period])->first();
            # get the opposite account
            if (empty($oppositeEntryAccount)) {
                # we could not find the account
                throw new \RuntimeException(
                    'We could not find the companion account for the double-entry financial record for '.
                    title_case($configuration[$period]).' in your list of accounts. Please try again.'
                );
            }
            $entry = null;
            DB::transaction(function () use ($account, $createdAt, &$entry, $oppositeEntryAccount, $oppositeEntryType, $request) {
                $entry = $account->entries()->create([
                    'entry_type' => $account->entry_type,
                    'currency' => strtoupper($request->input('currency', 'NGN')),
                    'amount' => $request->amount,
                    'memo' => $request->input('memo', null),
                    'source_type' => $request->input('source_type', 'manual'),
                    'source_info' => $request->input('source_info', 'manual'),
                    'created_at' => $createdAt->format('Y-m-d H:i:s')
                ]);
                # create the first entry
                $oppositeEntryAccount->entries()->create([
                    'entry_type' => $oppositeEntryType,
                    'currency' => strtoupper($request->input('currency', 'NGN')),
                    'amount' => $request->amount,
                    'memo' => $request->input('memo', null),
                    'source_type' => $request->input('source_type', 'manual'),
                    'source_info' => $request->input('source_info', 'manual'),
                    'created_at' => $createdAt->format('Y-m-d H:i:s')
                ]);
                # create the second entry
            });
        }
        $resource = new Item($entry, new AccountingEntryTransformer(), 'entry');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    public function createBulk(Request $request, Manager $fractal)
    {
        $this->validate($request, [
            'account' => 'required|string',
            'entries' => 'required|array',
            'entries.*.currency' => 'nullable|string|size:3',
            'entries.*.amount' => 'required|numeric',
            'entries.*.memo' => 'nullable|string|max:300',
            'entries.*.source_type' => 'nullable|string',
            'entries.*.source_info' => 'nullable|string|max:300',
            'entries.*.created_at' => 'nullable|date_format:Y-m-d',
            'entries.*.double_entry_type' => 'nullable|string',
            'entries.*.double_entry_period' => 'nullable|in:future,present',
        ]);
        # validate the request
        $doubleEntryCompanion = [
            'credit' => ['future' => 'accounts receivable', 'present' => 'cash'],
            'debit' => ['future' => 'accounts payable', 'present' => 'cash']
        ];
        # the configuration for loading appropriate account
        $company = $this->company($request);
        # get the company
        $account = $company->accountingAccounts()->where('uuid', $request->account)->first();
        if (empty($account)) {
            throw new RecordNotFoundException('Could not find the specified account to add the entry in.');
        }
        $now = Carbon::now();
        $entryCollection = [];
        $entries = $request->input('entries');
        $oppositeEntryDictionary = [];
        $oppositeEntriesCollection = [];
        foreach ($entries as $entryData) {
            $doubleEntryType = data_get($entryData, 'double_entry_type');
            $createdTime = data_get($entryData, 'created_at');
            $createdAt = empty($createdTime) ? $now : Carbon::createFromFormat('Y-m-d', $createdTime);
    
            $entryCollection[] = [
                'entry_type' => $account->entry_type,
                'currency' => strtoupper(data_get($entryData, 'currency', 'NGN')),
                'amount' => data_get($entryData, 'amount'),
                'memo' => data_get($entryData, 'memo'),
                'source_type' => data_get($entryData, 'source_type', 'manual'),
                'source_info' => data_get($entryData, 'source_info', 'manual'),
                'created_at' => $createdAt->format('Y-m-d H:i:s')
            ];
            
            if (!empty($doubleEntryType)) {
                # we have it specified
                $period = strtolower(data_get($entryData, 'double_entry_period', 'present'));
                # we assume everything is in the present
                $configuration = $doubleEntryCompanion[$doubleEntryType];
                # the configuration
                $oppositeEntryType = $doubleEntryType === 'debit' ? 'credit' : 'debit';
                # set the opposite entry type
                if (empty($oppositeEntryDictionary[$configuration[$period]])) {
                    $oppositeEntryAccount = $company->accountingAccounts()->where('name', $configuration[$period])->first();
                    # get the opposite account
                    $oppositeEntryDictionary[$configuration[$period]] = $oppositeEntryAccount;
                } else {
                    $oppositeEntryAccount = $oppositeEntryDictionary[$configuration[$period]];
                }
                # get the opposite account
                if (empty($oppositeEntryAccount)) {
                    # we could not find the account
                    throw new \RuntimeException(
                        'We could not find the companion account for the double-entry financial record for '.
                        title_case($configuration[$period]).' in your list of accounts. Please try again.'
                    );
                }
                
                $oppositeEntriesCollection[$configuration[$period]][] = [
                    'entry_type' => $oppositeEntryType,
                    'currency' => strtoupper(data_get($entryData, 'currency', 'NGN')),
                    'amount' => data_get($entryData, 'amount'),
                    'memo' => data_get($entryData, 'memo'),
                    'source_type' => data_get($entryData, 'source_type', 'manual'),
                    'source_info' => data_get($entryData, 'source_info', 'manual'),
                    'created_at' => $createdAt->format('Y-m-d H:i:s')
                ];
            }
        }
        $createdEntries = $account->entries()->createMany($entryCollection);
        if (!empty($oppositeEntriesCollection)) {
            foreach ($oppositeEntriesCollection as $name => $entriesToSave) {
                $account = $oppositeEntryDictionary[$name] ?? null;
                if (empty($account)) {
                    continue;
                }
                $account->entries()->createMany($entriesToSave);
            }
        }
        # create all the entries
        $resource = new Collection($createdEntries, new AccountingEntryTransformer(), 'entry');
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
        $entry = $company->accountingEntries()->whereRaw('`accounting_entries`.`uuid`=?', [$id])->firstOrFail();
        # get the entry
        if (!(clone $entry)->delete()) {
            throw new DeletingFailedException(
                'Errors while deleting the specified accounting entry. Please try again.'
            );
        }
        $transformer = new AccountingEntryTransformer();
        $transformer->setDefaultIncludes([]);
        # adjust the default includes
        $resource = new Item($entry, $transformer, 'entry');
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
        $entry = $company->accountingEntries()->whereRaw('`accounting_entries`.`uuid`=?', [$id])->firstOrFail();
        # get the account
        $resource = new Item($entry, new AccountingEntryTransformer(), 'entry');
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
            'account' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'amount' => 'nullable|numeric',
            'memo' => 'nullable|string|max:300',
            'source_type' => 'nullable|string',
            'source_info' => 'nullable|string|max:300',
            'created_at' => 'nullable|date_format:Y-m-d'
        ]);
        # validate the request
        $company = $this->company($request);
        # get the company
        $entry = $company->accountingEntries()->whereRaw('`accounting_entries`.`uuid`=?', [$id])->firstOrFail();
        # get the entry
        if ($request->has('account')) {
            $account = $company->accountingAccounts()->where('uuid', $request->account)->first();
            if (empty($account)) {
                throw new RecordNotFoundException('Could not find the specified account to add the entry in.');
            }
            $entry->account_id = $account->id;
        }
        if ($request->has('created_at')) {
            $createdAt = Carbon::createFromFormat('Y-m-d', $request->input('created_at'));
            $request->request->set('created_at', $createdAt->format('Y-m-d H:i:s'));
        }
        $this->updateModelAttributes($entry, $request);
        # update the attributes
        $entry->saveOrFail();
        # save the changes
        $resource = new Item($entry, new AccountingEntryTransformer(), 'entry');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}