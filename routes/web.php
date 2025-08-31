<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return redirect()->route('documents.index');
});

Route::resource('documents', DocumentController::class);

// Preview routes
Route::get('documents/{id}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
Route::get('documents/{id}/quick-preview', [DocumentController::class, 'quickPreview'])->name('documents.quick-preview');
Route::get('documents/{id}/print', [DocumentController::class, 'print'])->name('documents.print');

// Share routes
Route::get('documents/{id}/share', [DocumentController::class, 'share'])->name('documents.share');
Route::get('documents/shared/{token}', [DocumentController::class, 'shared'])->name('documents.shared');


Route::get('documents/download/{id}', [DocumentController::class, 'download'])->name('documents.download');
//Route::post('documents/callback/{id}', [DocumentController::class, 'callback'])->name('documents.callback');

// Pastikan callback route accessible dari external
Route::post('documents/callback/{id}', [DocumentController::class, 'callback'])
    ->name('documents.callback')
    ->withoutMiddleware(['web']); // Bypass some web middleware
