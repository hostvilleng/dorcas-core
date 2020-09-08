<?php

namespace App\Http\Controllers\Approval;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ApprovalAuthorizers;
use App\Models\AuthorizerRequestGrants;
use App\Models\Company;
use App\Models\Approvals;
use App\Transformers\ApprovalAuthorizersTransformer;
use App\Transformers\ApprovalsTransformer;
use App\Transformers\ApprovalTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Mockery\Exception;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class ApprovalAuthorizer extends Controller
{
    /**
     * @var array
     */
    protected $updateFields = [
        'approval_scope' => 'approval_scope',
    ];
    const SCOPE = ['critical','standard'];

    private $authorizers;
    private $grants;
    public function __construct(ApprovalAuthorizers $authorizers, AuthorizerRequestGrants $grants)
    {
        $this->authorizers =   $authorizers;
        $this->grants = $grants;
    }


    /**
     * @param Request      $request
     * @param Manager      $fractal
     * @param string       $id
     * @param Company|null $company
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */


    public function create(Request $request,Manager $fractal ){
        # get the company
        $this->validate($request, [
            'approval_id' => 'required',
            'approval_scope' => 'required',
            'user' => 'required_without:users|string',
            'users' => 'required_without:user|array',
            'users.*' => 'string'
        ]);
        if ($request->has('users')) {
            $users = $request->input('users');
        } else {
            $users = [$request->input('users')];
        }
        $company = $this->company($request);
        # retrieve the company

        $approval = $company->approvals()->where('uuid', $request->approval_id)->firstOrFail();
        $listing = $company->users()->whereIn('uuid', $users)
            ->whereNotIn('users.id', function ($query) use ($approval) {
                $query->select('user_id')
                    ->from('approval_authorizers')
                    ->where('approval_id', $approval->id);
            })
            ->get();

        $approval->authorizers()->attach($listing->pluck('id'),['approval_scope'=>$request->approval_scope]);

        # attach these employees
        $resource = new Item($approval, new ApprovalsTransformer(),'approvals');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }


    public function delete(Request $request, Manager $fractal){
        $company = $this->company($request);
        $this->validate($request, [
            'approval_id' => 'required',
            'user' => 'required_without:users|string',
            'users' => 'required_without:user|array',
            'users.*' => 'string'
        ]);
        if ($request->has('users')) {
            $users = $request->input('user');
        } else {
            $users = [$request->input('user')];
        }
        $company = $this->company($request);
        # retrieve the company
        $approval = $company->approvals()->where('uuid', $request->approval_id)->firstOrFail();
        # try to get the group
        $listing = $company->users()->whereIn('uuid', $users)->pluck('id');
        # get the matching employees
        $approval->authorizers()->detach($listing);
        # attach these employees
        $resource = new Item($approval, new ApprovalsTransformer(),'approvals');
        # create the resource
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }


}