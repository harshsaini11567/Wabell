<?php

use App\Domains\Admin\User\Controllers\AdminController;
use App\Domains\Admin\User\Controllers\MasterController;
use App\Domains\Admin\User\Controllers\CustomerController;
use App\Domains\Admin\User\Controllers\VerifiedMasterController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

Route::get('learners', [CustomerController::class, "index"])->name('customers.index');
Route::get('learners/show/{id}', [CustomerController::class, "show"])->name('customers.show');
Route::post('learners/isban', [CustomerController::class, "isBan"])->name('customers.isban');
Route::get('learners/edit/{id}', [CustomerController::class, "edit"])->name('customers.edit');
Route::post('learners/edit/{id}', [CustomerController::class, "update"])->name('customers.update');
Route::delete('learners/{id}/delete', [CustomerController::class, "destroy"])->name('customers.destroy');
Route::post('learners/change-status', [CustomerController::class, "changeStatus"])->name('customers.status');

Route::get('admins/change-password/{id}', [AdminController::class, "changePassword"])->name('admins.change-password');
Route::post('admins/change-password/{id}', [AdminController::class, "changePasswordSubmit"])->name('admins.change-password');
Route::post('admins/change-status', [AdminController::class, "changeStatus"])->name('admins.status');

Route::post('admins/masters/change-status', [MasterController::class, "changeStatus"])->name('masters.status');
Route::post('admins/master/isapproved', [MasterController::class, "isMasterApproved"])->name('masters.isapproved');
Route::post('admins/master/isban', [MasterController::class, "isBan"])->name('masters.isban');

Route::post('admins/verified-masters/change-status', [VerifiedMasterController::class, "changeStatus"])->name('verified-masters.status');
Route::post('admins/verified-master/isapproved', [VerifiedMasterController::class, "isMasterApproved"])->name('verified-masters.isapproved');
Route::post('admins/verified-master/approval-status', [VerifiedMasterController::class, "isVerifiedMasterApproved"])->name('verified-masters.approval-status');
Route::post('admins/verified-master/isban', [VerifiedMasterController::class, "isBan"])->name('verified-masters.isban');

Route::resource('masters', MasterController::class);
Route::resource('verified-masters', VerifiedMasterController::class);
Route::resource('admins', AdminController::class);