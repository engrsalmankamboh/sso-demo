<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::view('/', 'sso'); // Home UI

Route::prefix('api/sso')->group(function () {
    Route::get('/providers', [SsoController::class, 'providers']);

    // Build redirect URLs (bulk + single)
    Route::get('/redirects', [SsoController::class, 'redirects']);
    Route::get('/redirect/{provider}', [SsoController::class, 'redirect']);

    // OAuth callback (now redirects to "/")
    Route::get('/callback/{provider}', [SsoController::class, 'callback'])->name('sso.callback');

    // Session & history
    Route::get('/me', [SsoController::class, 'me']);
    Route::post('/logout', [SsoController::class, 'logout']);

    // 🆕 test runs & history exposure/clear
    Route::get('/tests', [SsoController::class, 'tests']);           // run tests for all providers & store results
    Route::get('/history', [SsoController::class, 'history']);       // get callback history
    Route::post('/history/clear', [SsoController::class, 'clearHistory']); // clear history & tests
});
