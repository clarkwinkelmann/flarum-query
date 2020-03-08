<?php

use Illuminate\Support\Facades\Route;

Route::get('/', AppController::class . '@home');
Route::get('/q/{uid}', AppController::class . '@query');
Route::resource('/api/queries', QueryController::class)->only('store');
Route::resource('/api/saved-queries', SavedQueryController::class)->only('store');
