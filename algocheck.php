<?php

foreach ($paygroups as $paygroup) {
    $paygroup_ids[] = $paygroup->id;
    if (!($paygroup->allowances)->isEmpty()) {
        foreach ($paygroup->allowances as $allowance) {
            $final_output[$employee->id] = $allowance;

            switch ($allowance->model) {
                case 'percent_of_base':
                    if(!$allowance->allowance_type === 'deduction'){
//                                        $base_salary += $this->processPercentage($allowance->model_data,$base_salary);
                    }
                    else{

//                                        $base_salary -= $this->processPercentage($allowance->model_data,$base_salary);
                    }
                    break;
                case 'fixed':

            }
        }
//                        $final_output[] = $employee->id .'--->' .$base_salary;

    }





//    switch ($allowance->model) {
//        case 'percent_of_base':
//            $employee_percent = (0.01 * $model_data->base_ratio );
//            $employer_percent = (0.01 * $model_data->employer_base_ratio );
//            $value  = ( $employee_percent * $base_salary ) + ($employer_percent * $base_salary);
//            if(!$allowance->allowance_type === 'deduction'){
//                $amount += $base_salary - $value;
//            }
//            else{
//                $amount += $base_salary + $value;
//            }
//            return $amount;
//            break;
//        case 'fixed':
//            $value = $model_data->fixed_value + $base_salary;
//            if(!$allowance->allowance_type === 'deduction'){
//                $amount -= $value;
//            }
//            else{
//                $amount += $value;
//            }
//            break;
//        case 'computational':
//            $amount = 0;
//    }
}