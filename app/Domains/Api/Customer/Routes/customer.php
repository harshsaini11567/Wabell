<?php

use App\Domains\Api\Customer\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('master-list', [CustomerController::class, 'masterList']);
Route::get('featured-master-list', [CustomerController::class, 'featuredMasterList']);
Route::get('/masters/search', [CustomerController::class, 'searchMasters']);
Route::post('/master/favorite/{masterId}',[CustomerController::class,'favoriteMaster']);
Route::get('/master/favorites', [CustomerController::class, 'listFavoritesMaster']);
Route::post('/masters/{masterId}/view', [CustomerController::class, 'viewMaster']);
Route::get('/master/profile/{masterId}',[CustomerController::class, 'masterProfile']);
Route::get('/filter-data', [CustomerController::class, 'getFilterData']);
Route::get('/masters/filter',[CustomerController::class, 'filterMaster']);
Route::post('/masters/request/{masterId}', [CustomerController::class, 'requestToMaster']);
Route::get('/get-specialities', [CustomerController::class, 'getSpecialities']);
Route::get('/get-child-specialities', [CustomerController::class, 'getChildSpecialities']);
Route::get('/search-masters', [CustomerController::class, 'searchMastersBySpecialities']);
Route::get('chat-requested-master', [CustomerController::class, 'listChatRequestedMaster']);
Route::get('call-requested-master', [CustomerController::class, 'listCallRequestedMaster']);

// Master routes
Route::get('favorite-customer', [CustomerController::class, 'listFavCustomer']);
Route::get('view-customer', [CustomerController::class, 'listViewCustomer']);
Route::get('requested-customers', [CustomerController::class, 'listRequestedCustomer']);

Route::get('chat-requested-customer', [CustomerController::class, 'listChatRequestedCustomer']);
Route::get('call-requested-customer', [CustomerController::class, 'listCallRequestedCustomer']);