<?php

namespace App\Providers;

//use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
        $gate->define('isAdmin', function($user)
        {
            return $user->role == 'admin';
        });

        $gate->define('isReception',function($user){
            return $user->role == 'reception';
        });

        $gate->define('isCashier', function($user){
            return $user->role == 'cashier';
        });

        $gate->define('isOpd1', function($user){
            return $user->role == 'opd1';
        });
        $gate->define('isOpd2', function($user){
            return $user->role == 'opd2';
        });
        $gate->define('isOpd3', function($user){
            return $user->role == 'opd3';
        });
        $gate->define('isOpd4', function($user){
            return $user->role == 'opd4';
        });
        $gate->define('isLab', function($user){
            return $user->role == 'Laboratoriest';
        });
        $gate->define('isAlt', function($user){
            return $user->role == 'Altrasound';
        });
        $gate->define('isPha',function($user){
            return $user->role == 'Pharmacist';
        });


        //
    }
}
