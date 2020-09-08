<?php

namespace App\Http\Controllers;

use App\Dorcas\Enum\ResponseStatus;
use App\Exceptions\RecordNotFoundException;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * The response container
     *
     * @var array
     */
    protected $data = [];

    /**
     * An associative array of request field names, and the model keys that should be used when trying to
     * update the columns in a model.
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * Returns the company for the currently authenticated user, returns NULL otherwise.
     *
     * @param Request|null $request
     * @param bool         $throwExceptionOnFail
     * @param bool         $enforcePlanAccess
     *
     * @return null|Company
     * @throws AuthorizationException
     * @throws RecordNotFoundException
     */
    protected function company(Request $request = null, bool $throwExceptionOnFail = true, bool $enforcePlanAccess = true)
    {
        if (empty($request)) {
            $request = app('request');
        }
        $user = $request->user();
        # get the user
        if (empty($user)) {
            if ($throwExceptionOnFail) {
                throw new RecordNotFoundException('Sorry, we cannot retrieve your company because you are not authenticated.');
            }
            return null;
        }
        $company = $user->company;
        if (empty($company) && $throwExceptionOnFail) {
            throw new RecordNotFoundException('Sorry, we could not retrieve the company information for your account.');
        }
        if (!$enforcePlanAccess) {
            # we're not checking plan access
            return $company;
        }
        $plan = $company->plan;
        # get the plan the company is on
        if ($plan->price_monthly > 0) {
            if (empty($company->access_expires_at)) {
                throw new AuthorizationException(
                    'You have not yet paid the subscription fee for your selected plan ('.title_case($plan->name).')'
                );

            } elseif (Carbon::now()->greaterThan($company->access_expires_at)) {
                throw new AuthorizationException(
                    'Your access for the '.title_case($plan->name) . ' plan expired on '.
                    $company->access_expires_at->format('D jS M, Y')
                );
            }
        }
        return $company;
    }

    /**
     * Accepts an eloquent model, and the current request instance, using the request data to update the
     * fields of the model based on the configuration of the updateFields array.
     *
     * @param Model   $model
     * @param Request $request
     */
    protected function updateModelAttributes(Model &$model, Request $request)
    {
        if (empty($this->updateFields)) {
            throw new \UnexpectedValueException('The update fields were not configured for the endpoint.');
        }
        foreach ($this->updateFields as $requestKey => $modelKey) {
            if (!$request->has($requestKey)) {
                # doesn't have it, so we skip the key
                continue;
            }
            $model->{$modelKey} = $request->input($requestKey);
        }
    }

    /**
     * Returns a properly formatted JSON response for a ValidationException
     *
     * @param ValidationException $e
     * @param int                 $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorToResponse(ValidationException $e, int $status = 400)
    {
        $response = [
            'status' => $status,
            'code' => ResponseStatus::VALIDATION_FAILED,
            'title' => 'Some validation errors were encountered while processing your request',
            'source' => validation_errors_to_messages($e)
        ];
        # convert the error
        return response()->json(['errors' => [$response]], $status);
    }

    /**
     * Returns a properly formatted JSON response for a Throwable
     *
     * @param \Throwable $e
     * @param int        $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function throwableToResponse(\Throwable $e, int $status = 500)
    {
        $response = [
            'status' => $status,
            'code' => ResponseStatus::EXCEPTION,
            'title' => $e->getMessage(),
        ];
        return response()->json(['errors' => [$response]], $status);
    }
}
