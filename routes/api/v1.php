<?php

use App\Http\Controllers\Api\V1\TestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/user', [TestController::class, 'index'])->middleware('auth:sanctum');
});
