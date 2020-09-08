<?php


namespace App\Http\Controllers\Approval;


use App\Models\Employee;
use App\Models\LeaveRequests;
use Carbon\Carbon;

trait RequestModel
{
    public function showRequest($model,$model_id)  {
        $model_name = explode('\\',$model);
        $model_data = null;
        switch ($model_name[2]){
            case 'LeaveRequests':
               $model_data =  $this->handleLeave($model_id);
                break;
            case 'PayrollRequests':
                $model_date = $this->handlePayroll($model_id);
                break;
            default:
                break;
        }
        return $model_data;
    }

    private function handleLeave($model_id){
        $leave = new LeaveRequests();
        $leaveRequests = $leave->findorFail($model_id);
        $employee = Employee::findorFail($leaveRequests->employee_id);
        $resource = [
            'request_type' => 'LEAVE REQUEST',
//            'request_id' => $leaveRequests->uuid,
            'Requester ful name' => $employee->firstname. ' '. $employee->lastname,
            'Requester staff code' => $employee->staff_code,
            'Requester Job Title' => $employee->job_title,
            'Requester email address' => $employee->email,
            'Requester phone number' => $employee->phone,
            'Amount of days utilized' => $leaveRequests->count_utilized,
            'Days left for leave request' => $leaveRequests->count_remaining,
            'Amount of days being requested' => $leaveRequests->count_requesting .'  days',
            'Starting date' => Carbon::parse( $leaveRequests->data_start_date)->format(' d M, Y '),
            'Date of resumption' =>Carbon::parse( $leaveRequests->data_report_back)->format(' d M, Y '),
            'Contact Address' => $leaveRequests->data_contact_address,
            'Contact Phone Number' => $leaveRequests->data_contact_phone,
            'Back up Staff' => $leaveRequests->data_backup_staff,
            'Requester Remarks' => $leaveRequests->data_remarks,
            'Date of creation' => Carbon::parse($leaveRequests->updated_at)->format(' d M, Y H:m'),
        ];
        return $resource;
    }

    private function handlePayroll($model_id){
        return;

    }

    private function handleFinance($model_id){
        return;

    }

    private function handleAnnonymous($model_id){
        return;
    }



}