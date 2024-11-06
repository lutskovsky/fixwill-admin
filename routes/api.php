<?php

use App\Http\Controllers\ComagicWebhookController;
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('webhook')->group(function () {
    Route::post('telegram', [TelegramController::class, 'handle']);
    Route::get('comagic', [ComagicWebhookController::class, 'handle']);
});

Route::get('/employee/{remonline_login}/virtual-numbers', [\App\Http\Controllers\EmployeeController::class, 'getVirtualNumbers']);
Route::get('/order/{orderLabel}/client', [\App\Http\Controllers\OrderController::class, 'getClient']);

