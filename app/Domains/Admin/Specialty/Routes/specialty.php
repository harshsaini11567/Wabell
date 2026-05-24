<?php

use App\Domains\Admin\Specialty\Controllers\SpecialtyController;
use App\Domains\Admin\Specialty\Controllers\SpecialtyRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/import-csv', [SpecialtyController::class, 'importCsv'])->name('import.csv');
Route::get('/specialties/export', [SpecialtyController::class, 'exportExcel'])->name('specialties.export');

Route::get('specialties/create/{id?}', [SpecialtyController::class, 'create'])->name('specialties.create');
Route::post('specialties/store/{id?}', [SpecialtyController::class, 'store'])->name('specialties.store');

Route::get('specialties/get-child-specialties/{id}', [SpecialtyController::class, 'getChildSpecialties'])->name('specialties.get-child-specialties');
Route::post('specialties-icon', [SpecialtyController::class, 'removeSpecialtyIcon'])->name('remove.specialty-icon');
Route::resource('specialties', SpecialtyController::class)->except(['store', 'create']);

Route::post('specialty-requests/Update-status', [SpecialtyRequestController::class, "updateStatus"])->name('specialty.request.status');
Route::resource('specialty-requests', SpecialtyRequestController::class);