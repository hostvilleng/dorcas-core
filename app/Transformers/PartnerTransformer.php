<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\Partner;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PartnerTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['companies', 'domain_issuances', 'users'];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['domain_issuances'];

    /**
     * @param Partner $partner
     *
     * @return array
     */
    public function transform(Partner $partner)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $partner->uuid,
            'name' => $partner->name,
            'slug' => $partner->slug,
            'logo' => $partner->logo,
            'extra_data' => !empty($partner->extra_data) ? $partner->extra_data : [],
            'is_verified' => $partner->is_verified,
            'is_trashed' => $partner->deleted_at !== null,
            'trashed_at' => !empty($partner->deleted_at) ? $partner->deleted_at->toIso8601String() : null,
            'updated_at' => !empty($partner->updated_at) ? $partner->updated_at->toIso8601String() : null,
            'created_at' => $partner->created_at->toIso8601String(),
        ];
    }

    /**
     * @param Partner       $partner
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompanies(Partner $partner, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $companies = $partner->companies()->take($limit)
                                            ->offset($offset)
                                            ->oldest('name')
                                            ->get();
        return $this->collection($companies, new CompanyTransformer(), 'company');
    }
    
    /**
     * @param Partner $partner
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeDomainIssuances(Partner $partner)
    {
        $issuances = $partner->domainIssuances;
        if (empty($issuances)) {
            return null;
        }
        return $this->collection($issuances, new DomainIssuanceTransformer(), 'domain_issuance');
    }

    /**
     * @param Partner       $partner
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeUsers(Partner $partner, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $users = $partner->users()->take($limit)
                                        ->offset($offset)
                                        ->oldest('firstname')
                                        ->oldest('lastname')
                                        ->get();
        $transformer = new UserTransformer();
        $transformer->setDefaultIncludes(['company', 'roles'])->setAvailableIncludes(['company', 'roles']);
        return $this->collection($users, $transformer, 'user');
    }
}