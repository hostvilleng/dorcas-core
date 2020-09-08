<?php


namespace App\Jobs\Approval;

use App\Jobs\Job;
use Illuminate\Support\Facades\Artisan;

class handleActions extends Job
{
    protected $request_id;
    protected $status;
    protected $model_type;

    protected $commands = [
        'LeaveRequests' => [
            'command' => 'dorcas:requests-leave-action'
        ],
        'PayrollRequests' => [
            'command' => 'dorcas:approvals-payroll-request-action'
        ],
    ];
    public function __construct($request_id,$status,$model_type)
    {
        $this->request_id = $request_id;
        $this->status = $status;
        $this->model_type = $model_type;
    }

    public function handle(){
        foreach ($this->commands as $key => $value) {
            if($this->model_type ===  $key){
                    Artisan::call($value['command'], [
                        'request_id' => $this->request_id,
                        'status' => $this->status,
                    ]);
               break;
            }
        }
    }

}