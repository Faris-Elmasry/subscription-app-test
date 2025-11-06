<?php

use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

