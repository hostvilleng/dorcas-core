<?php

namespace App\Http\Controllers\Approval;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\ApprovalAuthorizers;
use App\Models\AuthorizerRequestGrants;
use App\Models\AuthorizerRequestMails;
use App\Models\Company;
use App\Models\Approvals;
use App\Models\User;
use App\Notifications\Approvals\sendRequest;
use Illuminate\Pagination\Paginator;
use App\Transformers\ApprovalAuthorizersTransformer;
use App\Transformers\ApprovalRequestsTransformer;
use App\Transformers\ApprovalsTransformer;
use App\Transformers\ApprovalTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Mockery\Exception;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Illuminate\Support\Facades\Notification;
use App\Events\Approvals\NewApprovalRequest;
class ApprovalRequests extends Controller
{
    /**
     * @var array
     */
    use AuthorizerRequest,RequestModel;

    /**
     * @param Request      $request
     * @param Manager      $fractal
     * @param string       $id
     * @param Company|null $company
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Manager $fractal)
    {
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        # retrieve the company
        $pagingAppends = ['limit' => $limit];

        $company = $this->company();
        # append values for the paginator
            # searching for something
        if (empty($search)) {
            $paginator = $company->approvalRequests()->latest()
                ->paginate($limit);
        }
        else{
                $paginator = \App\Models\ApprovalRequests::search($search)
                ->where('company_id', $company->id)
                ->paginate($limit);
        }

         # get the orders
        $resource = new Collection($paginator->getCollection(), new ApprovalRequestsTransformer(), 'approvalRequests');
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


    //shows all requests for an authorizer

    public function authorizersIndex(Request $request, Manager $fractal){
        $search = $request->query('search');
        # get the search term in the query, if any
        $limit = $request->query('limit', 10);
        # the maximum number of customers to return
        # retrieve the company
        $pagingAppends = ['limit' => $limit];

        $company = $this->company();
        $user = $company->users()->where('uuid',$request->query('user_id'))->firstOrFail();
        $approvalRequests = $this->getRequests($user);
        if($approvalRequests->isEmpty()){
            throw new NotFoundHttpException('no current request awaits your approval');
        }
        $builder = $approvalRequests;

        $resource = new Collection($builder, new ApprovalRequestsTransformer(), 'approvalRequests');
        # add the append terms
        # set the paginator
        return response()->json($fractal->createData($resource)->toArray(),200);
    }

    public function single(Request $request, Manager $fractal, string $id){
        $company = $this->company($request);
        # retrieve the company
        $approvalRequest = $company->approvalRequests()->where('uuid', $id)->firstOrFail();
        # try to get the group
        $model_data = $this->showRequest($approvalRequest->model,$approvalRequest->model_request_id);
        $model_data = json_encode($model_data);
        $approvalRequest->update(['model_data'=>$model_data]);
        $resource = new Item($approvalRequest, new ApprovalRequestsTransformer(), 'approvalRequest');
        # get the resource
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function action(Request $request){
        $company = $this->company();
        # get the company
        $this->validate($request, [
            'request_id' => 'required',
            'user_id' => 'required',
            'status' => 'required'
        ]);
        $user = $company->users()->where('uuid',$request->user_id)->firstOrFail();
        $approvalRequest = $company->approvalRequests()->where('uuid',$request->request_id)->firstOrFail();
        $action = AuthorizerRequestGrants::create([
           'authorizer_id' => $user->id,
           'request_id' => $approvalRequest->id,
            'status' => $request->status,
        ]);
        if($request->has('rejection_comment')){
            $rejection_comments =  json_decode($approvalRequest->rejection_comments,true);
            $rejection_comments[] = ['authorizer'=>$user->firstname, 'comment' => $request->rejection_comment];
            $json = json_encode($rejection_comments);
            $approvalRequest->rejection_comments = $json;
            $approvalRequest->save();

        }

        return response()->json(['status'=> 'approval action has been saved successfully'],201);
    }





}
