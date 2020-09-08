<?php

namespace App\Http\Controllers\Directory;


use App\Exceptions\DeletingFailedException;
use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Transformers\ProfessionalExperienceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class Experiences extends Controller
{
    /** @var array  */
    protected $updateFields = [
        'company' => 'company',
        'designation' => 'designation',
        'from_year' => 'from_year',
        'to_year' => 'to_year',
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
            'company' => 'required|max:80',
            'designation' => 'required|string|max:50',
            'from_year' => 'required|date_format:Y',
            'to_year' => 'nullable|date_format:Y',
        ]);
        # validate the request
        $experience = $user->professionalExperiences()->create([
            'company' => $request->company,
            'designation' => $request->designation,
            'from_year' => $request->from_year,
            'to_year' => $request->has('to_year') ? $request->to_year : null,
        ]);
        # create the model
        $resource = new Item($experience, new ProfessionalExperienceTransformer(), 'professional_experience');
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
        $experience = $user->professionalExperiences()->where('uuid', $id)->firstOrFail();
        # find the model
        if (!(clone $experience)->delete()) {
            throw new DeletingFailedException('Failed while deleting the experience. Please try again.');
        }
        $resource = new Item($experience, new ProfessionalExperienceTransformer(), 'professional_experience');
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
            'company' => 'nullable|max:80',
            'designation' => 'nullable|string|max:50',
            'from_year' => 'nullable|date_format:Y',
            'to_year' => 'nullable|date_format:Y',
            'is_current' => 'nullable'
        ]);
        # validate the request
        $experience = $user->professionalExperiences()->where('uuid', $id)->firstOrFail();
        # find the model
        $this->updateModelAttributes($experience, $request);
        # update the attributes
        if ($request->is_current) {
            # it's the current job/experience
            $experience->to_year = null;
        }
        $experience->saveOrFail();
        # save the changes
        $resource = new Item($experience, new ProfessionalExperienceTransformer(), 'professional_experience');
        return response()->json($fractal->createData($resource)->toArray(), 200);
    }
}