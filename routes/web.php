<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::name('subscribe.')
->group(function(){
    Route::get('/', [SubscriptionController::class, 'show'])->name('show');

    Route::post('/', [SubscriptionController::class, 'store'])->name('store');

    Route::get('/approval', [SubscriptionController::class, 'approval'])->name('approval');

    Route::get('/cancelled', [SubscriptionController::class, 'cancelled'])->name('cancelled');
});
