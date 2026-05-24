<?php

use App\Domains\Admin\City\Controllers\CityController;
use App\Domains\Admin\City\Controllers\NeighborhoodController;
use Illuminate\Support\Facades\Route;

Route::get('/users-without-location', [CityController::class, 'userWithoutLocation'])->name('cities.user_without_location');
Route::get('cities/user/{uuid}', [CityController::class, 'showUser'])->name('cities.user.show');

Route::resource('cities.neighborhoods', NeighborhoodController::class);
Route::post('/cities-import-csv', [CityController::class, 'importCsv'])->name('city.import.csv');
Route::resource('cities', CityController::class);