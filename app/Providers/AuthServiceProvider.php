<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\Game' => 'App\Policies\GamePolicy',
        'App\Models\AccountType' => 'App\Policies\AccountTypePolicy',
        'App\Models\AccountInfo' => 'App\Policies\AccountInfoPolicy',
        'App\Models\AccountAction' => 'App\Policies\AccountActionPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
