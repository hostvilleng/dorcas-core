<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\User;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'bank_accounts',
        'company',
        'company_access_grants',
        'domains',
        'invites',
        'partner',
        'professional_credentials',
        'professional_experiences',
        'professional_services',
        'professional_service_requests',
        'roles',
        'vendor_services',
    ];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['company', 'partner', 'roles'];

    /**
     * @param User $user
     *
     * @return array
     */
    public function transform(User $user)
    {
        $extras = [];
        if ($user->is_professional) {
            $extras['is_professional_verified'] = !empty($user->extra_configurations) && !empty($user->extra_configurations['professional_is_verified']);
        }
        return array_merge([
            'embeds' => $this->getEmbeds(),
            'id' => $user->uuid,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'password' => $user->password,
            'token' => $user->remember_token,
            'user_type' => $user->user_type,
            'gender' => $user->gender,
            'phone' => $user->phone,
            'photo' => $user->photo,
            'is_partner' => $user->is_partner,
            'is_employee' => $user->is_employee,
            'approval_scope' => $user->approval_scope,
            'is_professional' => $user->is_professional,
            'is_vendor' => $user->is_vendor,
            'is_verified' => $user->is_verified,
            'extra_configurations' => $user->extra_configurations ?: [],
            'created_at' => $user->created_at->toIso8601String(),
            'links' => [
                'self' => url('/users', [$user->uuid])
            ]
        ], $extras);
    }
    
    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeBankAccounts(User $model)
    {
        return $this->collection($model->bankAccounts, new BankAccountTransformer(), 'bank_account');
    }

    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCompany(User $model)
    {
        $company = $model->company()->withTrashed()->first();
        if (empty($company)) {
            return null;
        }
        return $this->item($company, new CompanyTransformer(), 'company');
    }
    
    /**
     * @param User          $user
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompanyAccessGrants(User $user, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $accessGrants = $user->companyAccessGrants()->take($limit)->offset($offset)->oldest()->get();
        $transformer = new UserAccessGrantTransformer();
        $transformer->setDefaultIncludes(['company']);
        return $this->collection($accessGrants, $transformer, 'access_grant');
    }
    
    /**
     * @param User          $model
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeDomains(User $model, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $domains = $model->domains()->take($limit)->offset($offset)->oldest('domain')->get();
        return $this->collection($domains, new DomainTransformer(), 'domain');
    }
    
    /**
     * @param User          $user
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeInvites(User $user, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $builder = $user->invites();
        $filter = $params->get('status');
        if (!empty($filter)) {
            $builder->whereIn('status', $filter);
        }
        $invites = $builder->take($limit)->offset($offset)->latest()->get();
        return $this->collection($invites, new InviteTransformer(), 'invite');
    }

    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Item|null
     */
    public function includePartner(User $model)
    {
        if (empty($model->partner_id)) {
            return null;
        }
        return $this->item($model->partner, new PartnerTransformer(), 'partner');
    }
    
    /**
     * @param User $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProfessionalCredentials(User $model)
    {
        return $this->collection($model->professionalCredentials, new ProfessionalCredentialTransformer(), 'professional_credential');
    }
    
    /**
     * @param User $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProfessionalExperiences(User $model)
    {
        return $this->collection($model->professionalExperiences, new ProfessionalExperienceTransformer(), 'professional_experience');
    }
    
    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProfessionalServices(User $model)
    {
        return $this->collection($model->professionalServices, new ProfessionalServiceTransformer(), 'professional_service');
    }
    
    /**
     * @param User          $model
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProfessionalServiceRequests(User $model, ParamBag $params = null)
    {
        list($limit, $offset) = parse_fractal_params($params, 0, 10);
        $requests = $model->professionalServiceRequests()->take($limit)->offset($offset)->latest()->get();
        return $this->collection($requests, new ServiceRequestTransformer(), 'professional_service_request');
    }
    
    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeRoles(User $model)
    {
        if (empty($model->roles)) {
            return null;
        }
        return $this->collection($model->roles, new RoleTransformer(), 'role');
    }
    
    /**
     * @param User $model
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeVendorServices(User $model)
    {
        return $this->collection($model->vendorServices, new ProfessionalServiceTransformer(), 'vendor_service');
    }
}