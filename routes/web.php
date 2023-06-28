<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/docs/postman/download', function () {
    $path = resource_path('docs/postman/Dropbox.postman_collection.json');

    return response()->download($path);
});

Route::get('/docs/postman/view', function () {
    $path = resource_path('docs/postman/Dropbox.postman_collection.json');

    return response()->file($path);
});


Route::get('/api-docs', function () {
    return view('documentation');
});
