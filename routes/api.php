<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MillController;
use App\Http\Controllers\YarnTypeController;
use App\Http\Controllers\InwardController;
use App\Http\Controllers\OutwardController;
use App\Http\Controllers\StateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ----------------- masters ----------------------------

Route::apiResource('customers', CustomerController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('items', ItemController::class);
Route::apiResource('mills', MillController::class);
Route::apiResource('yarn_types', YarnTypeController::class);
Route::apiResource('states', StateController::class);

Route::get('state_list', [StateController::class, 'StateSelectList']);
Route::get('customer_list', [CustomerController::class, 'CustomerSelectList']);
Route::get('mill_list', [MillController::class, 'MillSelectList']);
Route::get('yarn_type_list', [YarnTypeController::class, 'YarnTypeSelectList']);
Route::get('item_list', [ItemController::class, 'ItemSelectList']);


// ----------------- inward ----------------------------

Route::get('inward', [InwardController::class, 'index']);
Route::post('inward_add', [InwardController::class, 'Store']);
Route::get('inward_create', [InwardController::class, 'InwardCreate']);
Route::get('inward_edit/{id}', [InwardController::class, 'InwardEdit']);
Route::put('inward_update/{id}', [InwardController::class, 'InwardUpdate']);

// ----------------- outward ----------------------------

Route::get('outward', [OutwardController::class, 'index']);
Route::post('outward_add', [OutwardController::class, 'Store']);
Route::get('outward_create', [OutwardController::class, 'OutwardCreate']);
Route::get('outward_edit/{id}', [OutwardController::class, 'OutwardEdit']);
Route::post('outward_update/{id}', [OutwardController::class, 'OutwardUpdate']);
