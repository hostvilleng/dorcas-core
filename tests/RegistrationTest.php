<?php

class RegistrationTest extends TestCase
{
    // use RefreshDatabase;

    // public function testExample()
    // {
    //     // $this->assertTrue(true);
    // }

    public function register_user()
    {
        // $this->assertTrue(true);
    }

    // public function testing_user_signup()
    // {
    //     $user = factory('App\Models\User')->create();

    //     $response = $this->post('/register', [
    //         'email' => $user->email.'32',
    //         'password' => 'password',
    //         'feature_select' => 'all',
    //         'lastname' => $user->lastname,
    //         'firstname' => $user->firstname,
    //         'phone' => $user->phone,
    //         'client_id' => 49,
    //         'client_secret' => '6PDB2VojSB4xmNcJFyGUU3RSTvwBnA9oh1KW9681'
    //     ]);

    //     // dd($response);
    //     $response->seeJson();
    // }

    public function test_user_login()
    {
        
        // $user = factory('App\Models\User')->create();

        $response = $this->post('/oauth/token',[
            'username' => 'armstrong.alva@yahoo.com32',
            'password' => 'password',
            'client_id' => 49,
            'client_secret' => '6PDB2VojSB4xmNcJFyGUU3RSTvwBnA9oh1KW9681',
            'grant_type' => 'password'
        ]);
        dd($response);
    }
    
}