<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\AdditionalCharge;
use App\Models\InvoiceTax;

class InvoiceController extends Controller
{
    /* ------------------------------------------------------------
        LIST INVOICES WITH SEARCH + PAGINATION
    ------------------------------------------------------------ */
    public function index(Request $request)
    {
        $count   = $request->limit;
        $page    = $request->curpage;
        $search  = $request->searchInput;
        $sorting = "desc";

        $query = Invoice::with('customer');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%$search%")
                  ->orWhere('id', 'LIKE', "%$search%")
                  ->orWhere('invoice_date', 'LIKE', "%$search%")
                  ->orWhere('invoice_total', 'LIKE', "%$search%")
                  ->orWhere('status', 'LIKE', "%$search%");
            })
            ->orWhereHas('customer', fn($q) =>
                $q->where('customer_name', 'LIKE', "%$search%")
            );
        }

        $total = $query->count();

        $data = $query->orderBy('id', $sorting)
                      ->skip($count * ($page - 1))
                      ->take($count)
                      ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    /* ------------------------------------------------------------
        SAFE INVOICE NUMBER (NO SKIP, NO DUPLICATE)
        USING DB::lockForUpdate()
    ------------------------------------------------------------ */
    public function InvoiceCreate()
    {
        DB::beginTransaction();

        $last = DB::table('invoices')
            ->lockForUpdate()
            ->orderBy('invoice_number', 'DESC')
            ->first();

        $nextNumber = $last ? $last->invoice_number + 1 : 1;

        DB::commit();

        return $nextNumber;
    }

    /* ------------------------------------------------------------
        STORE NEW INVOICE (React sends balance amount)
        amount_received = initial only, editable
    ------------------------------------------------------------ */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();

            /* ------- Secure Invoice Number (NO SKIP) ------- */
            $last = DB::table('invoices')
                ->lockForUpdate()
                ->orderBy('invoice_number', 'DESC')
                ->first();

            $nextNumber = $last ? $last->invoice_number + 1 : 1;
            $input['invoice_number'] = $nextNumber;

            /* ------- Create Invoice ------- */
            $invoice = Invoice::create($input);

            /* ------- Details ------- */
            foreach ($request->invoice_details ?? [] as $detail) {
                $detail['invoice_id'] = $invoice->id;
                $detail['user_id']    = $input['user_id'];
                InvoiceDetail::create($detail);
            }

            /* ------- Additional Charges ------- */
            foreach ($request->additional_charges ?? [] as $charge) {
                $charge['invoice_id'] = $invoice->id;
                $charge['user_id']    = $input['user_id'];
                AdditionalCharge::create($charge);
            }

            /* ------- Taxes ------- */
            foreach ($request->invoice_taxes ?? [] as $tax) {
                $tax['invoice_id'] = $invoice->id;
                $tax['user_id']    = $input['user_id'];
                InvoiceTax::create($tax);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ------------------------------------------------------------
        EDIT INVOICE WITH RELATIONS
    ------------------------------------------------------------ */
    public function InvoiceEdit($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);

        $invoice['Items'] = InvoiceDetail::with('item')
                            ->where('invoice_id', $id)
                            ->get();

        $invoice['AdditionalCharges'] = AdditionalCharge::where('invoice_id', $id)->get();

        $invoice['InvoiceTaxes'] = InvoiceTax::where('invoice_id', $id)->get();

        return response($invoice);
    }

    /* ------------------------------------------------------------
        UPDATE INVOICE (Initial amount_received editable)
        React updates balance_amount, controller does NOT modify.
    ------------------------------------------------------------ */
    public function InvoiceUpdate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();
            $invoice = Invoice::findOrFail($id);

            $invoice->update($input);

            /* ------- Delete Old Relations ------- */
            InvoiceDetail::where('invoice_id', $id)->delete();
            AdditionalCharge::where('invoice_id', $id)->delete();
            InvoiceTax::where('invoice_id', $id)->delete();

            /* ------- Insert New Details ------- */
            foreach ($request->invoice_details ?? [] as $detail) {
                $detail['invoice_id'] = $invoice->id;
                $detail['user_id']    = $input['user_id'];
                InvoiceDetail::create($detail);
            }

            /* ------- Additional Charges ------- */
            foreach ($request->additional_charges ?? [] as $charge) {
                $charge['invoice_id'] = $invoice->id;
                $charge['user_id']    = $input['user_id'];
                AdditionalCharge::create($charge);
            }

            /* ------- Taxes ------- */
            foreach ($request->invoice_taxes ?? [] as $tax) {
                $tax['invoice_id'] = $invoice->id;
                $tax['user_id']    = $input['user_id'];
                InvoiceTax::create($tax);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage(),
            ], 500);
        }
    }
}
