<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\EmployeeCallController;
use App\Http\Controllers\OrderClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/order/{orderLabel}/client', [OrderClientController::class, 'show'])->name('order.client.show');
    Route::get('/order/new', [OrderClientController::class, 'show'])->name('order.new');
    Route::post('/order/create', [OrderClientController::class, 'updateOrCreate'])->name('order.create');
    Route::post('/client/{clientId}/update', [OrderClientController::class, 'updateOrCreate'])->name('order.client.update');
    Route::get('/virtual_numbers', [RegisteredUserController::class, 'getVirtualNumbers'])->name('user.virtual_numbers.get');
    Route::post('employee-call', [EmployeeCallController::class, 'handle'])->name('employee.call');

    Route::get('/report', [ReportController::class, 'show'])
        ->name('report.show')
        ->middleware('can:view reports');
});


require __DIR__ . '/auth.php';
