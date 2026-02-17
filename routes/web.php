<?php

use App\Http\Controllers\WebhookHubController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function (): void {
    Route::get('/webhook-hub', [WebhookHubController::class, 'index'])->name('webhook-hub.index');
    Route::get('/webhook-hub/calls/{uuid}', [WebhookHubController::class, 'show'])->name('webhook-hub.show');
});
