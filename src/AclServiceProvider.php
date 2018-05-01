<?php

namespace dmcdenissen\acl;

use Exception;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/config/acl.php' => config_path('acl.php')
        ], 'config');
        $this->registerPermissions($gate);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Dynamically register permission with Laravel's gate
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * 
     * @return void
     */
    private function registerPermissions(GateContract $gate)
    {
        foreach ($this->permissions() as $permission) {
            $gate->define($permission->name, function ($user) use ($permission) {
                return $user->hasPermission($permission->name);
            });
        }
    }

    /**
     * Fetch the collection of site permissions.
     *
     * @return Collection
     */
    private function permissions()
    {
        if (app()->runningInConsole()) {
            return [];
        }
        $permission = app()->make(config('acl.model.permission'));

        if (!$permission instanceof \Illuminate\Database\Eloquent\Model) {
            throw new Exception('Class ' . config('acl.model.permission') . ' must be instance of Illuminate\\Database\\Eloquent\\Model');
        }
        return $permission->with('roles')->get();
    }

}
