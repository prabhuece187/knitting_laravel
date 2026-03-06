<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\AdditionalCharge;
use App\Models\InvoiceTax;

class InvoiceController extends BaseController
{

/* ------------------------------------------------------------
   LIST INVOICES (USER BASED)
------------------------------------------------------------ */
public function index(Request $request)
{
    $page   = (int) $request->get('page', 1);
    $limit  = (int) $request->get('limit', 10);
    $search = $request->get('search');

    $query = Invoice::with('customer')
        ->select('invoices.*')
        ->where('invoices.user_id', auth()->id());   // ✅ USER BASED

    if (!empty($search)) {

        $query->where(function ($q) use ($search) {

            $q->where('invoices.invoice_number', 'like', "%{$search}%")
            ->orWhere('invoices.id', 'like', "%{$search}%")
            ->orWhere('invoices.invoice_date', 'like', "%{$search}%")
            ->orWhere('invoices.invoice_total', 'like', "%{$search}%")
            ->orWhere('invoices.balance_amount', 'like', "%{$search}%")

            ->orWhereHas('customer', function ($qc) use ($search) {
                $qc->where('customer_name', 'like', "%{$search}%");
            });

        });
    }

    $query->orderBy('invoices.id', 'desc');

    return response()->json(
        $this->paginate($query, $page, $limit)
    );
}


/* ------------------------------------------------------------
   SAFE INVOICE NUMBER
------------------------------------------------------------ */
public function InvoiceCreate()
{
    DB::beginTransaction();

    $last = DB::table('invoices')
        ->where('user_id', auth()->id())
        ->lockForUpdate()
        ->orderBy('invoice_number', 'DESC')
        ->first();

    $nextNumber = $last ? $last->invoice_number + 1 : 1;

    DB::commit();

    return $nextNumber;
}


/* ------------------------------------------------------------
   STORE INVOICE
------------------------------------------------------------ */
public function store(Request $request)
{

    DB::beginTransaction();

    try {
        $input = $request->all();
        /* ---------- Secure Invoice Number ---------- */

        $last = DB::table('invoices')
            ->where('user_id', auth()->id())
            ->lockForUpdate()
            ->orderBy('invoice_number', 'DESC')
            ->first();

        $nextNumber = $last ? $last->invoice_number + 1 : 1;

        $input['invoice_number'] = $nextNumber;
        $input['user_id'] = auth()->id();   // ✅ SECURE


        /* ---------- Create Invoice ---------- */
        $invoice = Invoice::create($input);

        /* ---------- Details ---------- */
        foreach ($request->invoice_details ?? [] as $detail) {
            $detail['invoice_id'] = $invoice->id;
            $detail['user_id'] = auth()->id();

            InvoiceDetail::create($detail);
        }

        /* ---------- Additional Charges ---------- */
        foreach ($request->additional_charges ?? [] as $charge) {
            $charge['invoice_id'] = $invoice->id;
            $charge['user_id'] = auth()->id();

            AdditionalCharge::create($charge);
        }

        /* ---------- Taxes ---------- */

        foreach ($request->invoice_taxes ?? [] as $tax) {
            $tax['invoice_id'] = $invoice->id;
            $tax['user_id'] = auth()->id();

            InvoiceTax::create($tax);
        }
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'invoice' => $invoice
        ], 201);

    }

    catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error saving invoice: '.$e->getMessage()
        ], 500);
    }

}


/* ------------------------------------------------------------
   EDIT INVOICE
------------------------------------------------------------ */

public function InvoiceEdit($id)
{

    $invoice = Invoice::with('customer')
        ->where('user_id', auth()->id())
        ->findOrFail($id);

    $invoice['Items'] = InvoiceDetail::with('item')
        ->where('invoice_id', $id)
        ->where('user_id', auth()->id())
        ->get();

    $invoice['AdditionalCharges'] = AdditionalCharge::where('invoice_id', $id)
        ->where('user_id', auth()->id())
        ->get();

    $invoice['InvoiceTaxes'] = InvoiceTax::where('invoice_id', $id)
        ->where('user_id', auth()->id())
        ->get();

    return response($invoice);
}


/* ------------------------------------------------------------
   UPDATE INVOICE
------------------------------------------------------------ */

public function InvoiceUpdate(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $input = $request->all();

        $invoice = Invoice::where('user_id', auth()->id())
            ->findOrFail($id);

        $invoice->update($input);

        /* ---------- Delete Old ---------- */

        InvoiceDetail::where('invoice_id', $id)->delete();
        AdditionalCharge::where('invoice_id', $id)->delete();
        InvoiceTax::where('invoice_id', $id)->delete();

        /* ---------- Insert New ---------- */

        foreach ($request->invoice_details ?? [] as $detail) {
            $detail['invoice_id'] = $invoice->id;
            $detail['user_id'] = auth()->id();

            InvoiceDetail::create($detail);
        }


        foreach ($request->additional_charges ?? [] as $charge) {
            $charge['invoice_id'] = $invoice->id;
            $charge['user_id'] = auth()->id();

            AdditionalCharge::create($charge);
        }


        foreach ($request->invoice_taxes ?? [] as $tax) {
            $tax['invoice_id'] = $invoice->id;
            $tax['user_id'] = auth()->id();

            InvoiceTax::create($tax);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'invoice' => $invoice
        ]);

    }

    catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error updating invoice: '.$e->getMessage()
        ], 500);

    }

}

}