<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        Vite::prefetch(concurrency: 3);


        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class, //this is package controller
            \App\Http\Controllers\Admin\UserCrudController::class //this should be your own controller
        );
        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest::class, //this is package controller
            \App\Http\Requests\UserUpdateCrudRequest::class //this should be your own controller
        );
        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest::class, //this is package controller
            \App\Http\Requests\UserStoreCrudRequest::class //this should be your own controller
        );
    }
}
