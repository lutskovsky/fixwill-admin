<?php

namespace App\Providers;

use App\Http\Controllers\Admin\UserCrudController;
use App\Http\Requests\UserStoreCrudRequest;
use App\Http\Requests\UserUpdateCrudRequest;
use App\Services\Comagic\ComagicChatService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ComagicChatService::class, function ($app) {
            return new ComagicChatService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        Vite::prefetch(concurrency: 3);

        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class, //this is package controller
            UserCrudController::class //this should be your own controller
        );
        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest::class, //this is package controller
            UserUpdateCrudRequest::class //this should be your own controller
        );
        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest::class, //this is package controller
            UserStoreCrudRequest::class //this should be your own controller
        );
    }
}
