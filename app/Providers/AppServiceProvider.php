<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $repositories = ['User'];

        array_map(function ($repository){
            return $this->app->bind(
                'App\Repositories\\'.$repository.'\\'.'UserRepositorylnterface',
                'App\Repositories\\'.$repository.'\\'.'UserRepository'
            );
        },$repositories);

    }
}
