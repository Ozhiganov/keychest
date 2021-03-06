<?php

namespace App\Providers;

use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\SubdomainManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        // Registering sub-components, services, managers.
        $this->app->bind(ServerManager::class, function($app){
            return new ServerManager($app);
        });
        $this->app->bind(SubdomainManager::class, function($app){
            return new SubdomainManager($app);
        });
        $this->app->bind(ScanManager::class, function($app){
            return new ScanManager($app);
        });
    }
}
