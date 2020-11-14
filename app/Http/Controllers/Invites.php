<?php

namespace App\Http\Controllers;


use App\Events\AccountRegistered;
use App\Exceptions\RecordNotFoundException;
use App\Models\Company;
use App\Models\Invite;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use App\Transformers\InviteTransformer;
use App\Transformers\UserTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Invites extends Controller
{
    /**
     * Gets the invite record.
     *
     * @param string $id
     *
     * @return Invite
     */
    private function getInvite(string $id): Invite
    {
        $invite = Invite::where('uuid', $id)->first();
        # get the invite record
        if (empty($invite)) {
            throw new RecordNotFoundException('Could not find the specified invite.');
        }
        return $invite;
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request, Manager $fractal, string $id)
    {
        validate_api_client($request);
        # validate the requesting API client
        $invite = $this->getInvite($id);
        # get the invite
        $resource = new Item($invite, new InviteTransformer(), 'invite');
        return response()->json($fractal->createData($resource)->toArray());
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \App\Models\User|\Illuminate\Http\JsonResponse|null
     * @throws \Throwable
     */
    public function respond(Request $request, Manager $fractal, string $id)
    {
        validate_api_client($request);
        # validate the requesting API client
        $this->validate($request, [
            'status' => 'required|in:accepted,rejected',
            'firstname' => 'required_if:status,accepted|string|max:30',
            'lastname' => 'required_if:status,accepted|string|max:30',
            'email' => 'required_if:status,accepted|email|unique:users',
            'password' => 'required_if:status,accepted|string',
            'business' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
        ]);
        # validate the request
        $invite = $this->getInvite($id);
        # get the invite
        if ($invite->status !== 'pending') {
            # this invite has already been responded to
            throw new \RuntimeException(
                'This invite has already been responded to. You can reach out to your contact and request another invite.'
            );
        }
        $invitingUser = $invite->inviting_user;
        # get the inviting user
        if ($request->input('status') === 'rejected') {
            # the invite was rejected
            $invite->status = 'rejected';
            $invite->saveorFail();
            # save the changes to the invite
            $resource = new Item($invite, new InviteTransformer(), 'invite');
            return response()->json($fractal->createData($resource)->toArray());
        }
        $userData = [
            'firstname' => $request->input('firstname', $invite->firstname),
            'lastname' => $request->input('lastname', $invite->lastname),
            'phone' => $request->input('phone'),
            'email' => $invite->email,
            'password' => Hash::make($request->input('password')),
            'is_verified' => 1
        ];
        if ($invite->inviter instanceof Partner) {
            # set the partner id
            $userData['partner_id'] = $invite->inviter_id;
        }
        $inviteConfig = $invite->config_data;
        # get the invite configuration
        $roles = $inviteConfig['roles'];
        # roles to set on the user
        $companyData = [];
        # the data arrays to pass
        if ($inviteConfig['action'] === 'invite_business') {
            $companyData = ['name' => $inviteConfig['business'], 'access_expires_at' => Carbon::now()];
            foreach ($inviteConfig['properties'] as $key => $value) {
                $companyData[$key] = $value;
            }
        } elseif ($inviteConfig['action'] === 'invite_user') {
            foreach ($inviteConfig['properties'] as $key => $value) {
                $userData[$key] = $value;
            }
            $userData['company_id'] = !empty($invitingUser) ? $invitingUser->company_id : null;
        }
        $plan = Plan::where('name', 'starter')->first();
        # get the plan
        $user = null;
        $company = null;
        # our models
        DB::transaction(function () use (&$company, $companyData, $invite, $plan, &$user, $userData, $roles) {
            if (!empty($companyData) && !empty($companyData['name'])) {
                $companyData = array_merge($companyData, ['plan_id' => $plan->id, 'plan_type' => 'monthly']);
                $company = Company::create($companyData);
                # save the company
                $userData['company_id'] = $company->id;
            }
            $user = User::create($userData);
            # we need to create the user
            if (!empty($roles)) {
                # set the roles on the user account
                $user->assignRole($roles);
            }
            $invite->status = 'accepted';
            $invite->save();
            # update the invite
        });
        if (!empty($user)) {
            event(new AccountRegistered($user, $company));
        }
        $resource = new Item($user, new UserTransformer(), 'user');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
}