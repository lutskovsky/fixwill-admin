<?php

use App\Http\Controllers\ComagicWebhookController;
use App\Http\Controllers\EmployeeCallController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportPresetController;
use App\Http\Controllers\StatusChangeController;
use App\Http\Controllers\TelegramBots\CallNotificationsBotController;
use App\Http\Controllers\TelegramBots\LogisticsBotController;
use App\Listeners\TransferIssueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::any('null/{path?}', function (Request $request) {
    Log::channel('comagic_chat')->info($request->all());
    return response()->noContent(200);
})->where('path', '.*');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('webhook')->group(function () {
    Route::prefix('fixcpa')->group(function () {
        Route::post('call', [EmployeeCallController::class, 'scenarioCall']);
    });
    Route::prefix('call')->group(function () {
        Route::get('notify', [ComagicWebhookController::class, 'notify']);
        Route::get('create-order', [ComagicWebhookController::class, 'create']);
        Route::get('outgoing', [ComagicWebhookController::class, 'outgoingCall']);
    });
    Route::get('comagic', [ComagicWebhookController::class, 'handle']);
    Route::get('courier_error', [ComagicWebhookController::class, 'reportCourierCallError']);

    Route::post('status-change', [StatusChangeController::class, 'store']);
    Route::prefix('telegram')->group(function () {
        Route::post('call_notifications', [CallNotificationsBotController::class, 'handle']);
        Route::post('logistics', [LogisticsBotController::class, 'handle']);
        Route::post('status', [TransferIssueNotification::class, 'getMessage']);
    });
});

Route::get('/employee/{remonline_login}/virtual-numbers', [EmployeeController::class, 'getVirtualNumbers']);
Route::get('/order/{orderLabel}/client', [OrderController::class, 'getClient']);
Route::get('report/fetch-orders', [ReportController::class, 'fetchOrders'])->name('report.orders');

// Route to get all report presets
Route::get('/report-presets', [ReportPresetController::class, 'index']);

// Route to store a new report preset
Route::post('/report-presets', [ReportPresetController::class, 'store'])->name('report.preset.store');
Route::delete('/report-presets/{id}', [ReportPresetController::class, 'delete'])->name('report.preset.delete');

