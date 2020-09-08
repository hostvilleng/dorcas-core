<?php
namespace App\Console\Commands\Requests;


use App\Events\Approvals\ApproveRequest;
use App\Events\Approvals\DeclineRequest;
use App\Events\Approvals\NewApprovalRequest;
use App\Mail\Reports\DailyReportEmail;
use App\Models\Domain;
use App\Models\DomainIssuance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\ApprovalRequests;
use App\Models\LeaveRequests;

class leaveAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:requests-leave-action
                            {request_id : the ID of the request}
                            {status : the status of the request (either approval or decline) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an action to approve leave request';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $request_id = $this->argument('request_id');
        $status = $this->argument('status');
        $approvalRequest = ApprovalRequests::find($request_id);
        $leaveRequest = LeaveRequests::find($approvalRequest->model_request_id);
        $output = array();
        switch ($status) {
            case 'accept':
                  $approvalRequest->update([
                    'approval_status' => 'approved',
                  ]);
                   $leaveRequest->status = 'approved';
                   $leaveRequest->save();
              $this->sendEmail($leaveRequest->employee_id,'approved');
                break;
            case 'reject':
                    $approvalRequest->update([
                      'approval_status' => 'declined',
                    ]);
                   $leaveRequest->status = 'declined';
                   $leaveRequest->rejection_comments = $approvalRequest->rejection_comments;
                   $leaveRequest->save();
                   $this->sendEmail($leaveRequest->employee_id,'declined');
               break;
            default:
                // code...
                break;
        }


    }

    private function sendEmail($emoloyee_id,$status){
        $emoloyee = Employee::find($emoloyee_id);
        if ($emoloyee){
          $user = User::find($emoloyee->user_id);
          if ($status === 'approved') {
            event(new ApproveRequest($user,'leave'));
          }
          event(new DeclineRequest($user,'leave'));
        }

    }
}
