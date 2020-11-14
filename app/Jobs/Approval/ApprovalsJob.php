<?php

namespace App\Jobs\Approval;
use App\Events\Approvals\NewApprovalRequest;
use App\Jobs\Job;
use App\Models\ApprovalAuthorizers;
use App\Models\ApprovalRequests;
use App\Models\Approvals;
use App\Models\AuthorizerRequestGrants;
use App\Models\AuthorizerRequestMails;
use App\Notifications\Approvals\sendRequest;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Zend\Mail\Exception\RuntimeException;


class ApprovalsJob extends Job
{
    public function handle(): void
    {
        try{
            $testArr = [];
            $builder = new Approvals();
            $builder->where('active',1)->chunk(200,function($approvals) use (&$testArr){
                foreach ($approvals as $approval){
                    switch ($approval->frequency_type){
                        case 'random':
                            $this->treatRandom($approval->id);
                            break;
                        case 'sequential':
                            $this->treatSequential($approval->id);
                            break;
                        default:
                            break;
                    }

                    $this->treatRequests($approval->id);
                }

            });

            return;
        }
        catch (Exception $ex){
            Log::error($ex->getMessage());
        }
    }


    private function treatRandom($approval_id): void
    {
        $builder = ApprovalRequests::where(['approval_id'=>$approval_id,'approval_status'=>'active']);
        $authorizers =  ApprovalAuthorizers::where('approval_id',$approval_id)->pluck('user_id');
        $builder->chunk(200,function ($builder) use ($authorizers){
            foreach ($builder as $request){
                foreach ($authorizers as $user){
                    $email_sent = AuthorizerRequestMails::where(['request_id'=>$request->id,'authorizer_id'=>$user,'mail_action'=>1])->exists();
                    if($email_sent) {
                        continue;
                    }
                    $this->sendEmail($request,$user);
                }
            }
        });

    }

    private function treatSequential($approval_id): void
    {
        $builder = ApprovalRequests::where(['approval_id'=>$approval_id,'approval_status'=>'active']);
        $authorizers = ApprovalAuthorizers::where('approval_id',$approval_id)->orderBy('created_at')->pluck('user_id');
        $builder->chunk(200,function ($builder) use ($authorizers){
            foreach ($builder as $request){
                foreach ($authorizers as $authorizer){
                    $email_sent = AuthorizerRequestMails::where(['request_id'=>$request->id,'authorizer_id'=>$authorizer,'mail_action'=>1])->exists();
                    if(!$email_sent) {
                        $this->sendEmail($request,$authorizer);
                        break ;
                    }
                    $isGrant = AuthorizerRequestGrants::where(['request_id'=>$request->id,'authorizer_id'=>$authorizer])->exists();
                    if (! $isGrant) {
                        break ;
                    }
                }
                continue;
            }
        });
    }

    private function sendEmail($request,$user_id): void
    {
       $user = User::find($user_id);
        try{
        event(new NewApprovalRequest($user,$request));
        //triggers the event to send mail to authorizers

        AuthorizerRequestMails::insert([
           'authorizer_id' => $user_id,
           'request_id' => $request->id,
           'mail_action' => 1,
       ]);
        }
        catch(\Exception $e){
          throw new RuntimeException($e->getMessage());
        }
    }


    private function treatRequests($approval_id): void
    {
        try {
            $builder = ApprovalRequests::where(['approval_id'=>$approval_id,'approval_status'=>'active']);
            $approval = Approvals::find($approval_id);
            $output = [];
            $builder->chunk(200,function ($requests) use (&$output,$approval) {
            foreach ($requests as $request){
                $isApproved = false;
                $noOfAuthorizers = $approval->authorizers->count();
                 $model_name = explode('\\',$request->model);
                switch ($approval->scope_type){
                    case 'key_person':
                        $success = $this->treatKeyPersonApproval($approval,$request);
                        if ($success !== null){
                            if(!$success){
                                $this->ApprovalAction($request->id,'reject',$model_name[2]);
                            }
                            $this->ApprovalAction($request->id,'accept',$model_name[2]);
                        }
                        $this->ApprovalAction($request->id,'reject',$model_name[2]);
                        break;
                    case 'min_number':
                        $success = $this->treatNumber($request,$noOfAuthorizers);
                        if ($success !== null){
                            if(!$success){
                                $this->ApprovalAction($request->id,'reject',$model_name[2]);
                            }
                            $this->ApprovalAction($request->id,'accept',$model_name[2]);
                        }
                        break;
                    case 'both':
                        $keyperson =  $this->treatKeyPersonApproval($approval,$request);
                        $number =   $this->treatNumber($request,$noOfAuthorizers);
                        if (!$keyperson === null || !$number === null){
                            if(! $keyperson && $number){
                                $this->ApprovalAction($request->id,'reject',$model_name[2]);
                            }
                            $this->ApprovalAction($request->id,'accept',$model_name[2]);
                        }
                        break;
                    default:
                       Log::info('No Scope Type Selected for the Approval '. $approval->title);
                        exit;
                }
            }

            });
        }
        catch (\UnexpectedValueException $e) {
            Log::info($e->getMessage());
        }
    }

    private function treatKeyPersonApproval($approval,$request)  {
        $authorizers = ApprovalAuthorizers::where('approval_id',$approval->id)->where('approval_scope','critical')->pluck('user_id');
        $authorizerActionsCount = AuthorizerRequestGrants::where(['request_id'=>$request->id])->count();
        if (count($authorizers) !== $authorizerActionsCount ){
            return null;
        }
        foreach ($authorizers as $authorizer){
            $grant = AuthorizerRequestGrants::where(['request_id'=>$request->id,'authorizer_id'=>$authorizer,'status'=>1])->exists();
            if(!$grant){
                return false;
                break;
            }
            continue;
        }
        return true;
    }

    private function treatNumber($request,$noOfAuthorizers)  {
        $authorizerActionsCount = AuthorizerRequestGrants::where(['request_id'=>$request->id])->count();
        if ($noOfAuthorizers !== $authorizerActionsCount ){
            return null;
        }
        $grantsCount = AuthorizerRequestGrants::where(['request_id'=>$request->id,'status'=>1])->count();
        return !($noOfAuthorizers !== $grantsCount);
    }

    protected function ApprovalAction($request_id,$status,$model_type): void
    {
        dispatch(new handleActions($request_id,$status,$model_type));
    }

}
