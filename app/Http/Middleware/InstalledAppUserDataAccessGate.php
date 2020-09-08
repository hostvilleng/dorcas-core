<?php

namespace App\Http\Middleware;


use App\Exceptions\ApplicationAccessDeniedException;
use App\Models\Application;
use Closure;
use Hashids\Hashids;
use Illuminate\Http\Request;

class InstalledAppUserDataAccessGate
{
    /**
     * This middleware just ensures that only users who have installed an application can have their account
     * authenticated from the application.
     * The only other way to gain access to such an app would be authenticating via the token URL mode.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (empty($user = $request->user())) {
            return $response;
        }
        $clientId = $request->input('client_id');
        # we get the client ID
        if (ends_with($request->path(), 'oauth/token') && !empty($clientId) && !is_numeric($clientId)) {
            # this was a login request -- if the app isn't installed by the user, we reject the request
            # we'll only bother about those clients that are not numeric -- all non-numeric client IDs are for external
            # apps
            list($clientId) = app(Hashids::class)->decode($clientId);
            # get the actual client id
            $application = Application::with(['user'])->where('oauth_client_id', $clientId)->first();
            # get the application
            $company = $user->company;
            # get the authenticated company
            if (!$application->is_published) {
                # probably in testing mode -- so, we'll only allow people in the same organisation as the creating account
                if ($company->id !== $application->user->company_id) {
                    throw new ApplicationAccessDeniedException(
                        'Only people invited into your organisation can be application testers.'
                    );
                }
                return $response;
            }
            $installCount = $company->applicationInstalls()->where('application_id', $application->id)->count();
            # has it been installed
            if ($installCount === 0) {
                # we throw an exception
                throw new ApplicationAccessDeniedException(
                    $application->name . ' has not yet been installed by this user.'
                );
            }
        }
        return $response;
    }
}