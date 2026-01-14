<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MillController;
use App\Http\Controllers\YarnTypeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\KnittingMachineController;
use App\Http\Controllers\JobMasterController;

use App\Http\Controllers\InwardController;
use App\Http\Controllers\OutwardController;

use App\Http\Controllers\KnittingProductionController;
use App\Http\Controllers\KnittingProductionReturnController;
use App\Http\Controllers\KnittingReworkController;

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\KnittingReportController;

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
Route::apiResource('items', ItemController::class);
Route::apiResource('mills', MillController::class);
Route::apiResource('yarn_types', YarnTypeController::class);
Route::apiResource('states', StateController::class);

Route::apiResource('banks', BankController::class);
Route::apiResource('knitting-machines', KnittingMachineController::class);

Route::get('state_list', [StateController::class, 'StateSelectList']);
Route::get('customer_list', [CustomerController::class, 'CustomerSelectList']);
Route::get('mill_list', [MillController::class, 'MillSelectList']);
Route::get('yarn_type_list', [YarnTypeController::class, 'YarnTypeSelectList']);
Route::get('item_list', [ItemController::class, 'ItemSelectList']);
Route::get('bank_list', [BankController::class, 'BankSelectList']);
Route::get('machine-select-list', [KnittingMachineController::class, 'MachineSelectList']);

Route::put('set_default/{id}', [BankController::class, 'setDefault']);


// ----------------- inward ----------------------------

Route::get('inward', [InwardController::class, 'index']);
Route::post('inward_add', [InwardController::class, 'Store']);
Route::get('inward_create', [InwardController::class, 'InwardCreate']);
Route::get('inward_edit/{id}', [InwardController::class, 'InwardEdit']);
Route::put('inward_update/{id}', [InwardController::class, 'InwardUpdate']);

Route::get('inward_list', [InwardController::class, 'InwardSelectList']);
Route::post('/inward-details/{id}/link-job-card', [InwardController::class, 'linkJobCard']);

// ----------------- outward ----------------------------

Route::get('outward', [OutwardController::class, 'index']);
Route::post('outward_add', [OutwardController::class, 'Store']);
Route::get('outward_create', [OutwardController::class, 'OutwardCreate']);
Route::get('outward_edit/{id}', [OutwardController::class, 'OutwardEdit']);
Route::put('outward_update/{id}', [OutwardController::class, 'OutwardUpdate']);

// ----------------- job master ----------------------------

Route::get('/job-masters/next-job-no', [JobMasterController::class, 'nextJobNo']);
Route::apiResource('job-master', JobMasterController::class);
Route::get('job_list', [JobMasterController::class,'JobSelectList']);
// ----------------- knitting production ----------------------------

Route::get('knitting_production', [KnittingProductionController::class, 'index']);
Route::post('knitting_production_add', [KnittingProductionController::class, 'store']);
Route::get('knitting_production_create', [KnittingProductionController::class, 'productionCreate']);
Route::get('knitting_production_edit/{id}', [KnittingProductionController::class, 'edit']);
Route::put('knitting_production_update/{id}', [KnittingProductionController::class, 'update']);

Route::get('knitting_production_list', [KnittingProductionController::class, 'selectList']);

Route::get('knit_next_pro_no', [KnittingProductionController::class, 'productionCreate']);

// ----------------- production return ----------------------------

Route::get('knitting_production_return', [KnittingProductionReturnController::class,'index']);
Route::post('knitting_production_return_add', [KnittingProductionReturnController::class,'store']);
// Route::get('knitting_production_return_create', [KnittingProductionReturnController::class,'returnCreate']);

Route::get('production-return/next-no', [KnittingProductionReturnController::class, 'returnCreate']);

Route::get('knitting_production_return_edit/{id}', [KnittingProductionReturnController::class,'edit']);
Route::put('knitting_production_return_update/{id}', [KnittingProductionReturnController::class,'update']);
Route::get('knitting_production_return_list', [KnittingProductionReturnController::class,'selectList']);

// ----------------- production rework ----------------------------

Route::get('knitting_rework', [KnittingReworkController::class,'index']);
Route::post('knitting_rework_add', [KnittingReworkController::class,'store']);
Route::get('knitting_rework_create/next-no', [KnittingReworkController::class,'reworkCreate']);
Route::get('knitting_rework_edit/{id}', [KnittingReworkController::class,'edit']);
Route::put('knitting_rework_update/{id}', [KnittingReworkController::class,'update']);
Route::get('knitting_rework_list', [KnittingReworkController::class,'selectList']);

// ----------------- wastage report ----------------------------

Route::get('/wastage/{jobId}', [KnittingWastageReportController::class, 'show']);

// ----------------- job ledger ----------------------------

Route::get('/ledger/{jobId}', [KnittingJobLedgerController::class, 'index']);
Route::post('/ledger', [KnittingJobLedgerController::class, 'store']);

// ----------------- invoice ----------------------------

Route::get('invoice', [InvoiceController::class, 'index']);
Route::post('invoice_add', [InvoiceController::class, 'store']);
Route::get('invoice_create', [InvoiceController::class, 'InvoiceCreate']);
Route::get('invoice_edit/{id}', [InvoiceController::class, 'InvoiceEdit']);
Route::put('invoice_update/{id}', [InvoiceController::class, 'InvoiceUpdate']);

// ----------------- Customer Payment  ----------------------------

Route::get('payments',       [PaymentController::class, 'index']);
Route::post('payment_add',               [PaymentController::class, 'store']);
Route::delete('payment_delete/{id}',     [PaymentController::class, 'destroy']);

Route::get('/customer-invoices/{id}', [PaymentController::class, 'getInvoicesByCustomer']);


// ----------------- report ----------------------------

Route::post('over-all-report', [ReportController::class, 'OverAllReport']);
Route::post('over-all-detail-report', [ReportController::class, 'OverAllDetailReport']);
Route::get('single_customer_data/{id}', [CustomerController::class, 'SingleCustomerData']);
Route::get('single_item_data/{id}', [ItemController::class, 'SingleItemData']);
Route::get('single_mill_data/{id}', [MillController::class, 'SingleMillData']);
Route::get('single_yarn_type_data/{id}', [YarnTypeController::class, 'SingleYarnTypeData']);
Route::get('single_bank_data', [BankController::class, 'SingleBankData']);
Route::get('single-machine/{id}', [KnittingMachineController::class, 'SingleMachineData']);

Route::post('customer-ledger-inout', [ReportController::class, 'CustomerLedgerInOut']);
Route::post('customer-ledger-itemwise', [ReportController::class, 'CustomerLedgerInOutItemWise']);
Route::post('customer-individual-item', [ReportController::class, 'CustomerIndividualItem']);


Route::post('item-stock-report', [ReportController::class, 'ItemStockReport']);
Route::post('item-stock-customerwise', [ReportController::class, 'ItemStockReportCustomerWise']);
Route::post('item-individual-customer', [ReportController::class, 'ItemIndividualCustomer']);


Route::post('mill-ledger-inout', [ReportController::class, 'MillLedgerInOut']);
Route::post('mill-ledger-itemwise', [ReportController::class, 'MillLedgerInOutItemWise']);
Route::post('mill-individual-item', [ReportController::class, 'MillIndivodualItem']);

Route::post('yarn-type-ledger', [ReportController::class, 'YarnTypeLedger']);
Route::post('yarn-individual-customer', [ReportController::class, 'YarnTypeIndividualCustomer']);

Route::post('reports/job-ledger', [KnittingReportController::class, 'jobLedger']);
Route::post('reports/wastage', [KnittingReportController::class, 'wastageReport']);
