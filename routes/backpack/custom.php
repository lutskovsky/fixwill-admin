<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('employee', 'EmployeeCrudController');
    Route::crud('virtual-number', 'VirtualNumberCrudController');
    Route::crud('courier', 'CourierCrudController');
    Route::crud('scenario', 'ScenarioCrudController');
    Route::post('status/sync', [
        'uses' => '\App\Http\Controllers\Admin\StatusCrudController@sync',
        'as' => 'sync.statuses',
    ])->middleware('web');
    Route::crud('status', 'StatusCrudController');
    Route::crud('order-type', 'OrderTypeCrudController');
}); // this should be the absolute last line of this file