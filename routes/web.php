<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Storage;

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

Route::get('/', [LogController::class, 'uploadForm']);
Route::get('/upload', [LogController::class, 'uploadForm']);
Route::post('/upload', [LogController::class, 'uploadSubmit']);
Route::get('/parse/{file}', [LogController::class, 'parse'])->name('parse');

Route::get('/download/{filename}', function ($filename) {
    $path = storage_path('app/public/logs/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
});