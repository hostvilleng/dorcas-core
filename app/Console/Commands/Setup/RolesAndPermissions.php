<?php

namespace App\Console\Commands\Setup;


use App\Dorcas\Enum\PermissionName;
use App\Dorcas\Enum\RoleName;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:roles-and-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the roles and permissions that are used within the application';

    protected $permissions = [
        PermissionName::OVERLORD,
        PermissionName::MANAGE_COMPANIES,
        PermissionName::MANAGE_COMPANY,
        PermissionName::MANAGE_MEMBERS,
        PermissionName::MANAGE_PARTNERS,
        PermissionName::MANAGE_SYSTEM_SETTINGS,
        PermissionName::MANAGE_USERS,
        PermissionName::VIEW_COMPANIES,
        PermissionName::VIEW_MEMBERS,
        PermissionName::VIEW_PARTNERS,
        PermissionName::VIEW_SYSTEM_SETTINGS,
        PermissionName::VIEW_USERS,
    ];

    /**
     * The available system roles
     *
     * @var array
     */
    protected $roles = [
        RoleName::OVERLORD => [
            'permissions' => [
                PermissionName::OVERLORD,
                PermissionName::MANAGE_COMPANIES,
                PermissionName::MANAGE_PARTNERS,
                PermissionName::MANAGE_SYSTEM_SETTINGS,
                PermissionName::MANAGE_USERS,
                PermissionName::VIEW_COMPANIES,
                PermissionName::VIEW_PARTNERS,
                PermissionName::VIEW_SYSTEM_SETTINGS,
                PermissionName::VIEW_USERS,
            ]
        ],
        RoleName::ADMINISTRATOR => [
            'permissions' => [
                PermissionName::MANAGE_COMPANIES,
                PermissionName::MANAGE_PARTNERS,
                PermissionName::MANAGE_SYSTEM_SETTINGS,
                PermissionName::MANAGE_USERS,
                PermissionName::VIEW_COMPANIES,
                PermissionName::VIEW_PARTNERS,
                PermissionName::VIEW_SYSTEM_SETTINGS,
                PermissionName::VIEW_USERS,
            ]
        ],
        RoleName::PARTNER => [
            'permissions' => [
                PermissionName::VIEW_MEMBERS,
                PermissionName::MANAGE_MEMBERS,
                PermissionName::VIEW_COMPANIES,
                PermissionName::MANAGE_COMPANIES,
            ]
        ]
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Setting up roles and permissions...');
        try {
            $permissions = $this->setupPermissions($this->permissions);
            # configure the permissions
            $this->line('Processing roles... Available: '.count($this->roles));
            foreach ($this->roles as $name => $configuration) {
                $this->info('Permission: '.$name);
                if (!is_array($configuration)) {
                    $this->warn('Configuration problems; configuration should be an array...');
                    continue;
                }
                $guards = $configuration['guards'] ?? (array) 'api';
                # get the guards for this role
                $this->table(['Guard'], [$guards]);
                # the guards
                $requiredPermissions = [];
                if (is_string($configuration['permissions'])) {
                    $requiredPermissions = (array) $configuration['permissions'];
                } elseif (is_array($configuration['permissions'])) {
                    $requiredPermissions = $configuration['permissions'];
                }
                if (empty($requiredPermissions)) {
                    $this->warn('Improperly configured permissions. It should either be a string or an array...');
                }
                $requiredPermissions = collect($requiredPermissions);
                $this->table(['Requested Permission'], $requiredPermissions->map(function ($p) { return [$p]; }));
                # the guards
                $this->line('Filtering the permissions, based on those that are available...');
                $allowedPermissions = $requiredPermissions->filter(function ($permission) use ($permissions) {
                    return in_array($permission, $permissions);
                });
                $this->table(['Allowed Permission'], $allowedPermissions->map(function ($p) { return [$p]; }));
                # the guards
                foreach ($guards as $guard) {
                    $this->line('Setting up for guard: '.$guard);
                    $role = Role::firstOrCreate([
                        'name' => $name,
                        'guard_name' => $guard
                    ]);
                    if ($role !== null) {
                        $this->info('created... setting up permissions');
                        $role->syncPermissions($allowedPermissions->all());
                    }
                }
                $this->info('Completed!');
            }

        } catch (\UnderflowException $e) {
            $this->error($e->getMessage());
        }
        return;
    }

    /**
     * Configures the permissions and returns a list of created ones
     *
     * @param array $permissions
     *
     * @return array
     * @throws \UnderflowException
     */
    protected function setupPermissions(array $permissions): array
    {
        if (empty($this->permissions)) {
            throw new \UnderflowException('There are no configurable permissions provided!');
        }
        $configured = [];
        $this->line('Processing permissions... Available: '.count($permissions));
        foreach ($this->permissions as $index => $config) {
            $this->info('Permission: '.$index);
            if (!(is_string($config) || is_array($config))) {
                # config should wither be a string, or an array/
                $this->warn('Configuration problems; value should either be a string or an array...');
                continue;
            }
            if (is_string($index)) {
                $name = $index;
            } elseif (is_string($config)) {
                $name = $config;
            } else {
                $name = $config['name'] ?? null;
            }
            $this->info('Discovered name: '.(string) $name);
            if (empty($name)) {
                continue;
            }
            $guards = ['api'];
            if (is_array($config)) {
                $guards = $config['guards'] ?? (array) 'api';
            }
            $this->table(['Guard'], [$guards]);
            foreach ($guards as $guard) {
                $this->line('Setting up for guard: '.$guard);
                $permission = Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard
                ]);
                if ($permission !== null) {
                    $this->info('Created!');
                }
                if (!in_array($name, $configured)) {
                    $configured[] = $name;
                }
            }
        }
        return $configured;
    }
}
