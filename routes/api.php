<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ProductController;
use Intervention\Image\Facades\Image;

Route::post('/uploads/chunk', [UploadController::class, 'uploadChunk']);
Route::post('/uploads/complete', [UploadController::class, 'completeUpload']);
Route::post('/products/import', [ProductImportController::class, 'import']);
Route::post('/products/importline', [ProductImportController::class, 'importline']);
Route::get('/attach-image', [ProductImageController::class, 'getUploads']);
Route::post('/uploads', [ProductController::class, 'attach']);


