<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Company extends Model
{
    use SoftDeletes, Searchable;

    protected $casts = [
        'extra_data' => 'array'
    ];

    protected $dates = ['access_expires_at', 'deleted_at'];

    protected $fillable = [
        'uuid',
        'plan_id',
        'plan_type',
        'reg_number',
        'name',
        'phone',
        'email',
        'website',
        'extra_data',
        'prefix',
        'logo_url',
        'access_expires_at',
    ];

    /**
     * Adds a 'logo' attribute on the model.
     *
     * @return string|null
     */
    public function getLogoAttribute()
    {
        return !empty($this->attributes['logo_url']) ? Storage::disk(config('filesystems.default'))->url($this->attributes['logo_url']) : null;
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adverts()
    {
        return $this->hasMany(Advert::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applicationInstalls()
    {
        return $this->hasMany(ApplicationInstall::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billPayments()
    {
        return $this->hasMany(BillPayment::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogCategories()
    {
        return $this->hasMany(BlogCategory::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogMedia()
    {
        return $this->hasMany(BlogMedia::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingAccounts()
    {
        return $this->hasMany(AccountingAccount::class);
    }

    public function unverifiedTransactions(){
        return $this->hasMany(UnverifiedTransactions::class);
    }

    public function TaxAuthority(){
        return $this->hasMany(TaxAuthority::class);
    }

    public function PayrollAuthority(){
        return $this->hasMany(PayrollAuthorities::class);
    }

    public function PayrollAllowances(){
        return $this->hasMany(PayrollAllowances::class);
    }

    public function PayrollTransactions(){
        return $this->hasMany(PayrollTransactions::class);

    }

    public function PayrollRun(){
    return $this->hasMany(PayrollRun::class);
}


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function accountingEntries()
    {
        return $this->hasManyThrough(
            AccountingEntry::class,
            AccountingAccount::class,
            'company_id',
            'account_id'
        );
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bankAccounts()
    {
        return $this->morphMany(BankAccount::class, 'bankable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contactFields()
    {
        return $this->hasMany(ContactField::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function deals()
    {
        return $this->hasManyThrough(Deal::class, Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function domains()
    {
        return $this->morphMany(Domain::class, 'domainable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function domainIssuances()
    {
        return $this->morphMany(DomainIssuance::class, 'domainable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function paygroup(){
        return $this->hasMany(PayrollPaygroup::class);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function invites()
    {
        return $this->morphMany(Invite::class, 'inviter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportConfigurations()
    {
        return $this->hasMany(AccountingReportConfiguration::class);
    }

    /**
     * @return BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot(['created_at']);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function professionalServiceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userAccessGrants()
    {
        return $this->hasMany(UserAccessGrant::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approvals()
    {
        return $this->hasMany(Approvals::class);
    }

    public function approvalRequests(){
        return $this->hasMany(ApprovalRequests::class);
    }

    public function leaveTypes(){
        return $this->hasMany(LeaveTypes::class);
    }

    public function leaveGroups(){
        return $this->hasMany(LeaveGroups::class);
    }

    public function leaveRequests(){
        return $this->hasMany(LeaveRequests::class);
    }

}