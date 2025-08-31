<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return redirect()->route('documents.index');
});

Route::resource('documents', DocumentController::class);
Route::get('documents/download/{id}', [DocumentController::class, 'download'])->name('documents.download');
//Route::post('documents/callback/{id}', [DocumentController::class, 'callback'])->name('documents.callback');

// Pastikan callback route accessible dari external
Route::post('documents/callback/{id}', [DocumentController::class, 'callback'])
    ->name('documents.callback')
    ->withoutMiddleware(['web']); // Bypass some web middleware
