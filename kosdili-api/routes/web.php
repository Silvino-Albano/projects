<?php
use App\Http\Controllers\Api\VerificationController;

use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/', function () {
    return view('welcome');
});
