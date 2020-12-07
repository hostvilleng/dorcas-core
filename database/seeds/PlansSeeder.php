<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $planList = [
            [
                'name' => 'starter',
                'price_monthly' => 0.00,
                'price_yearly' => 0.00,
            ],
              [
                'name' => 'classic',
                'price_monthly' => 5000.00,
                'price_yearly' => 5000.00,
              ],
                 [
                'name' => 'access_advantage',
                'price_monthly' => 8000.00,
                'price_yearly' => 8000.00,
              ],
              
        ];

        foreach($planList as $key => $value){
            DB::table('plans')
            ->updateOrInsert([
                'name' => $planList[$key]['name']
            ],
            [
            'uuid' => Uuid::uuid1(),
            'name'=> $planList[$key]['name'],
            'price_monthly' => $planList[$key]['price_monthly'],
            'price_yearly' => $planList[$key]['price_yearly'],
            'deleted_at' => null,
            'updated_at'=> Carbon::now(),
            'created_at'=> Carbon::now(),
            ]);
        }
   
    }
}
