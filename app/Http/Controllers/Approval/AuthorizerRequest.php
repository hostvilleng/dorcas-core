<?php


namespace App\Http\Controllers\Approval;


use App\Models\ApprovalAuthorizers;
use App\Models\Approvals;
use App\Models\AuthorizerRequestGrants;
use Illuminate\Support\Collection;
use App\Models\ApprovalRequests;
trait AuthorizerRequest
{


    public function getRequests($user)  : Collection {
       $final_requests = array();
       $listing = ApprovalAuthorizers::where(function ($query) use ($user){
           $query->select('approval_id')
               ->from('approval_authorizers')
               ->where('user_id',$user->id);
       })->pluck('approval_id');

       $approvalRequests = ApprovalRequests::whereIn('approval_id',$listing)->where('company_id',$user->company_id)
        ->whereNotIn('id',function ($query) use ($user){
            $query->select('request_id')
                ->from('authorizer_request_grants')
                ->where('authorizer_id',$user->id);
        })
        ->get();
        foreach ($approvalRequests as $approvalRequest){
            $approval = Approvals::where(['active'=>1,'id'=>$approvalRequest->approval_id,'company_id'=>$user->company_id])->first();
            if($approval !== null) {
                switch ($approval->frequency_type) {
                    case 'random':
                        $final_requests[] = $approvalRequest;
                        break;
                    case 'sequential':
                          $isGrant = $this->checkGrants($approvalRequest,$user);
                          if($isGrant){
                              $final_requests[] = $approvalRequest;
                          }
                        break;
                    default:
                        break;
                }
            }
            continue;
        }

        return collect($final_requests)->map(function ($final){
            return (object) $final;
        });
   }


    private function checkGrants($request,$authorizer){

       /* iterate through authorizers list to get the current authorizers authorization details for the request*/
        $userRequest =    ApprovalAuthorizers::where(function ($query) use ($request,$authorizer){
            $query->select('user_id')
                ->from('approval_authorizers')
                ->where('approval_id',$request->approval_id)
                ->where('user_id',$authorizer->id);
        })
            ->first();
/*        1st Check
        iterate through all the authorizers to get the authorizers before the current authorizer and check if an
        authorization has been made for the request,
        if authorization has been made, show the request for the current authorizer else return null*/

        $authorizers_before =    ApprovalAuthorizers::where(function ($query) use ($request,$userRequest){
            $query->select('user_id')
                ->from('approval_authorizers')
                ->where('approval_id',$request->approval_id)
                ->where('user_id','!=',$userRequest->user_id)
                ->where('created_at','<',$userRequest->created_at);
        })
            ->pluck('user_id');
   /*     2nd Check
        checks if the previous authorizers have performed actions on the request*/
        $treated = false;
        if(! $authorizers_before->isEmpty()){
            foreach ($authorizers_before as $authorizer_now){
                $treated = AuthorizerRequestGrants::where('authorizer_id',$authorizer_now)->where('request_id',$request->id)->exists();
                if ($treated === true){
                    continue;
                }
                return false;
            }
            return true;
        }
            return true;
     /*   condition to return request to current authorizer if it's his turn or not
        */

    }
}