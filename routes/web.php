<?php

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

use Modules\TelegramApi\app\Http\Controllers\TelegramApiController;

Route::group(['middleware' => []], function () {

    Route::prefix('telegram-api')->group(function () {

        Route::post('login', [TelegramApiController::class, 'login'])->name('telegram-api.login');

    });

});
