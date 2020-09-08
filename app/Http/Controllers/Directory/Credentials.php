<?php

namespace App\Http\Controllers\Directory;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\ProfessionalCredentialTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Credentials extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'title' => 'title',
        'type' => 'type',
        'description' => 'description',
        'year' => 'year',
        'certification' => 'certification',
    ];
    
    /**
     * @param Request $request
     * @param Manager $fractal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, Manager $fractal)
    {
        $user = $request->user();
        # get the user
        if (!$user->is_professional) {
            throw new RecordNotFoundException(
                'The professional directory function has not been enabled on this account.'
            );
        }
        $this->validate($request, [
            'title' => 'required|max:80',
            'type' => 'required|string',
            'description' => 'nullable',
            'year' => 'required|date_format:Y',
            'certification' => 'required|string',
        ]);
        # validate the request
        $credential = $user->professionalCredentials()->create([
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'year' => $request->year,
            'certification' => $request->certification
        ]);
        # create the model
        $resource = new Item($credential, new ProfessionalCredentialTransformer(), 'professional_credential');
        return response()->json($fractal->createData($resource)->toArray(), 201);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Manager $fractal, string $id)
    {
        $user = $request->user();
        # get the user
        $credential = $user->professionalCredentials()->where('uuid', $id)->firstOrFail();
        # find the model
        if (!(clone $credential)->delete()) {
            throw new DeletingFailedException('Failed while deleting the credential. Please try again.');
        }
        $resource = new Item($credential, new ProfessionalCredentialTransformer(), 'professional_credential');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
    
    /**
     * @param Request $request
     * @param Manager $fractal
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Manager $fractal, string $id)
    {
        $user = $request->user();
        # get the user
        $this->validate($request, [
            'title' => 'nullable|max:80',
            'type' => 'nullable|string',
            'description' => 'nullable',
            'year' => 'nullable|date_format:Y',
            'certification' => 'nullable|string',
        ]);
        # validate the request
        $credential = $user->professionalCredentials()->where('uuid', $id)->firstOrFail();
        # find the model
        $this->updateModelAttributes($credential, $request);
        # update the attributes
        $credential->saveOrFail();
        # save the changes
        $resource = new Item($credential, new ProfessionalCredentialTransformer(), 'professional_credential');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}