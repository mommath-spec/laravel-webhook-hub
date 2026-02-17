<?php

use App\Http\Controllers\WebhookReceiverController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/{endpointKey}', [WebhookReceiverController::class, 'handle']);
