<?php

use App\Http\Controllers\Api\BiometricPunchController;
use Illuminate\Support\Facades\Route;

Route::post('/biometric/punch', [BiometricPunchController::class, 'punch'])->name('api.biometric.punch');
