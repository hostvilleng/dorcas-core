<?php /** @noinspection ALL */

namespace App\Providers;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingReportConfiguration;
use App\Models\ApprovalAuthorizers;
use App\Models\Approvals;
use App\Models\ApprovalRequests;
use App\Models\Advert;
use App\Models\Application;
use App\Models\ApplicationCategory;
use App\Models\ApplicationInstall;
use App\Models\BankAccount;
use App\Models\BlogCategory;
use App\Models\BlogMedia;
use App\Models\BlogPost;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactField;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Department;
use App\Models\Domain;
use App\Models\DomainIssuance;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Integration;
use App\Models\Invite;
use App\Models\LeaveGroups;
use App\Models\LeaveRequests;
use App\Models\LeaveTypes;
use App\Models\Location;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PaymentTransaction;
use App\Models\PayrollAllowances;
use App\Models\PayrollAuthorities;
use App\Models\PayrollPaygroup;
use App\Models\PayrollRun;
use App\Models\PayrollTransactions;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\ProfessionalCategory;
use App\Models\ProfessionalCredential;
use App\Models\ProfessionalExperience;
use App\Models\ProfessionalService;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\State;
use App\Models\TaxAuthority;
use App\Models\TaxElements;
use App\Models\TaxRuns;
use App\Models\Team;
use App\Models\User;
use App\Models\UserAccessGrant;
use App\Models\VendorService;
use App\Observers\AccountCodeObserver;
use App\Observers\BlogMediaObserver;
use App\Observers\UuidAndSlugObserver;
use App\Observers\PartnerObserver;
use App\Observers\UuidObserver;
use Dusterio\LumenPassport\LumenPassport;
use Hashids\Hashids;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        AccountingAccount::observe(UuidObserver::class);
        AccountingEntry::observe(UuidObserver::class);
        AccountingReportConfiguration::observe(UuidObserver::class);
        Approvals::observe(UuidObserver::class);
        ApprovalAuthorizers::observe(UuidObserver::class);
        ApprovalRequests::observe(UuidObserver::class);
        Advert::observe(UuidObserver::class);
        Application::observe(UuidObserver::class);
        ApplicationCategory::observe(UuidAndSlugObserver::class);
        ApplicationInstall::observe(UuidObserver::class);
        BankAccount::observe(UuidObserver::class);
        BlogCategory::observe(UuidAndSlugObserver::class);
        BlogMedia::observe(BlogMediaObserver::class);
        BlogPost::observe(UuidAndSlugObserver::class);
        Company::observe(UuidObserver::class);
        ContactField::observe(UuidObserver::class);
        Contact::observe(UuidObserver::class);
        Country::observe(UuidObserver::class);
        Coupon::observe(UuidObserver::class);
        CouponUsage::observe(UuidObserver::class);
        Customer::observe(UuidObserver::class);
        CustomerNote::observe(UuidObserver::class);
        Deal::observe(UuidObserver::class);
        DealStage::observe(UuidObserver::class);
        Department::observe(UuidObserver::class);
        Domain::observe(UuidObserver::class);
        DomainIssuance::observe(UuidObserver::class);
        Employee::observe(UuidObserver::class);
        Group::observe(UuidObserver::class);
        Integration::observe(UuidObserver::class);
        Invite::observe(UuidObserver::class);
        Location::observe(UuidObserver::class);
        Partner::observe(PartnerObserver::class);
        Product::observe(UuidObserver::class);
        ProductCategory::observe(UuidObserver::class);
        ProductImage::observe(UuidObserver::class);
        ProductPrice::observe(UuidObserver::class);
        ProfessionalCategory::observe(UuidObserver::class);
        ProfessionalCredential::observe(UuidObserver::class);
        ProfessionalExperience::observe(UuidObserver::class);
        ProfessionalService::observe(UuidObserver::class);
        ServiceRequest::observe(UuidObserver::class);
        Service::observe(UuidObserver::class);
        State::observe(UuidObserver::class);
        Order::observe(UuidObserver::class);
        PaymentTransaction::observe(UuidObserver::class);
        Permission::observe(UuidObserver::class);
        Plan::observe(UuidObserver::class);
        Role::observe(UuidObserver::class);
        \Spatie\Permission\Models\Role::observe(UuidObserver::class);
        Team::observe(UuidObserver::class);
        User::observe(UuidObserver::class);
        UserAccessGrant::observe(UuidObserver::class);
        VendorService::observe(UuidObserver::class);
        TaxAuthority::observe(UuidObserver::class);
        TaxElements::observe(UuidObserver::class);
        TaxRuns::observe(UuidObserver::class);
        PayrollAuthorities::observe(UuidObserver::class);
        PayrollAllowances::observe(UuidObserver::class);
        PayrollPaygroup::observe(UuidObserver::class);
        PayrollTransactions::observe(UuidObserver::class);
        PayrollRun::observe(UuidObserver::class);
        LeaveTypes::observe(UuidObserver::class);
        LeaveGroups::observe(UuidObserver::class);
        LeaveRequests::observe(UuidObserver::class);
        AccountingAccount::observe(AccountCodeObserver::class);

        # add model observers
        LumenPassport::routes($this->app);
        LumenPassport::allowMultipleTokens();
        # register the routes
        $this->app['path.config'] = base_path('config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        # add Fractal to the container
        $this->app->singleton(Manager::class, function () {
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer());
            $request = app('request');
            if ($request->query->has('include')) {
                $fractal->parseIncludes($request->query->get('include', ''));
            }
            return $fractal;
        });

        //always log  to bugsnag

        //$this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        //$this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);

        /*$this->app->extend(\Psr\Log\LoggerInterface::class, function ($logger, $app) {
            return new \Bugsnag\BugsnagLaravel\MultiLogger([$logger, $app['bugsnag.logger']]);
        });*/
        
        $this->app->singleton(Hashids::class, function () {
            return new Hashids('Dorcas Production API', 10);
        });
        


    }
}
