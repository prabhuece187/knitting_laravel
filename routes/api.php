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
use App\Http\Controllers\ReportController;

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
Route::put('outward_update/{id}', [OutwardController::class, 'OutwardUpdate']);

// ----------------- report ----------------------------

Route::post('over-all-report', [ReportController::class, 'OverAllReport']);
Route::post('over-all-detail-report', [ReportController::class, 'OverAllDetailReport']);
Route::get('single_customer_data/{id}', [CustomerController::class, 'SingleCustomerData']);
Route::get('single_item_data/{id}', [ItemController::class, 'SingleItemData']);
Route::get('single_mill_data/{id}', [MillController::class, 'SingleMillData']);
Route::get('single_yarn_type_data/{id}', [YarnTypeController::class, 'SingleYarnTypeData']);


Route::post('customer-ledger-inout', [ReportController::class, 'CustomerLedgerInOut']);
Route::post('customer-ledger-itemwise', [ReportController::class, 'CustomerLedgerInOutItemWise']);

Route::post('item-stock-report', [ReportController::class, 'ItemStockReport']);
Route::post('item-stock-customerwise', [ReportController::class, 'ItemStockReportCustomerWise']);


Route::post('mill-ledger-inout', [ReportController::class, 'MillLedgerInOut']);
Route::post('mill-ledger-itemwise', [ReportController::class, 'MillLedgerInOutItemWise']);

Route::post('yarn-type-ledger', [ReportController::class, 'YarnTypeLedger']);
