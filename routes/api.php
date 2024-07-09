<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactController;

Route::apiResource('contacts', ContactController::class);
Route::get('contacts/{id}/history', [ContactController::class, 'history']);