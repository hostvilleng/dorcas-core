<?php

namespace App\Http\Controllers\Auth;


use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class Authorize extends Controller
{
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorizeUserByEmail(Request $request)
    {
        validate_api_client($request);
        # validate the request
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        # validate the request
        $user = User::where('email', $request->input('email'))->first();
        # get the user account
        if (empty($user)) {
            throw new RecordNotFoundException('Could not find the user account.');
        }
        $token = $user->createToken(uniqid() . $user->uuid)->accessToken;
        # create the token for the user
        return response()->json(['access_token' => $token]);
    }
}