<?php
use Laravel\Lumen\Testing\DatabaseMigrations;
class RegistrationTest extends TestCase
{
  use DatabaseMigrations;

     public function testing_user_signup()
     {

         $user = factory('App\Models\User')->create();
         $response = $this->post('/register', [
             'email' => $user->email.'32',
             'password' => 'password',
             'feature_select' => 'all',
             'lastname' => $user->lastname,
             'firstname' => $user->firstname,
             'phone' => $user->phone,
             'client_id' => env('CLIENT_ID'),
             'client_secret' => env('CLIENT_SECRET')
         ]);
       $response->assertResponseOk(200);
     }

    public function test_user_login()
    {


        $response = $this->post('/oauth/token',[
            'username' => env('TEST_LOGIN_EMAIL'),
            'password' =>  env('TEST_LOGIN_PASSWORD'),
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'grant_type' => 'password'
        ]);
          $response->assertResponseOk(200);
//          $this->assertEquals(env('TEST_LOGIN_EMAIL'),$this->response->getOriginalContent()['data']['email']);
    }
    
}