<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use PlanSeeder;

class Init extends Controller {

    public function setup(){
    $client = $this->handlePassportKeys();
    $this->handlePlans();
    return response()->json(['message' => 'requirements fully setup','client_id' => $client->id,'client_secret' => $client->secret],201);
    }

    private function handlePassportKeys(){
    try {
    $name = config('app.name').' Personal Access Client';
    $redirect = env('CLIENT_SETUP_REDIRECT') ?? 'http://localhost';
    return  (new ClientRepository())->createPasswordGrantClient(null,$name,$redirect);
    }
    catch(\Exception $e){
        throw new Exception($e->getMessage());
    }

    }

    private function handlePlans(){
        $seeder = new PlanSeeder();
        $seeder->run();
    }
}