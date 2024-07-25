<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ComagicWebhookController;

Route::redirect('/', '/admin');

Route::post('/telegram/webhook', [TelegramController::class, 'handle']);
