<?php

use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
// use App\Http\Controllers\Web\HealthCheckResultsController;

Route::view('/', 'web.pages.welcome');
Route::get('/health', HealthCheckResultsController::class);
