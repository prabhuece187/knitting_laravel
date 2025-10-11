<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\AdditionalCharge;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Listing with search + pagination
    public function index(Request $request)
    {
        $invoice = $request->all();

        $count   = $invoice['limit'];
        $page    = $invoice['curpage'];
        $search  = $invoice['searchInput'];
        $sorting = "desc";

        $query = Invoice::with('customer');

        if (!empty($search)) {
            $query = $query->where(function ($q) use ($search) {
                $q->where('invoices.invoice_number', 'LIKE', "%$search%")
                  ->orWhere('invoices.id', 'LIKE', "%$search%")
                  ->orWhere('invoices.invoice_date', 'LIKE', "%$search%")
                  ->orWhere('invoices.invoice_total', 'LIKE', "%$search%")
                  ->orWhere('invoices.status', 'LIKE', "%$search%");
            })
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%$search%");
            });
        }

        $total = $query->count();

        $data = $query->orderBy('invoices.id', $sorting)
                      ->take($count)
                      ->skip($count * ($page - 1))
                      ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    // Auto-generate next invoice number
    public function InvoiceCreate(Request $request)
    {
        $data = Invoice::select('invoice_number')->orderBy('invoice_number', 'DESC')->first();
        return isset($data) ? ($data->invoice_number + 1) : 1;
    }

    // Store new invoice with details + additional charges
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Merge Auth user_id automatically
            $input = $request->all();
            // $input['user_id'] = Auth::id();

            // Create invoice
            $invoice = Invoice::create($input);

            // Invoice details
            $details = $request->invoice_details ?? [];
            foreach ($details as $detail) {
                $detail['invoice_id'] = $invoice->id;
                $detail['user_id'] = $input['user_id'];
                InvoiceDetail::create($detail);
            }

            // Additional charges (new key)
            $charges = $request->additional_charges ?? [];
            foreach ($charges as $charge) {
                $charge['invoice_id'] = $invoice->id;
                $charge['user_id'] = $input['user_id'];
                AdditionalCharge::create($charge);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving invoice: ' . $e->getMessage(),
            ], 500);
        }     
    }


    // Edit invoice with relations
    public function InvoiceEdit($id)
    {
        $invoice = Invoice::with('customer')->find($id);

        $invoice['Items'] = InvoiceDetail::with('item')
                            ->where('invoice_id', $id)->get();

        $invoice['AdditionalCharges'] = AdditionalCharge::where('invoice_id', $id)->get();

        return response($invoice);
    }

    // Update invoice + refresh details and charges
    public function InvoiceUpdate(Request $request, $id)
    {
        $input = $request->all();
        $invoice = Invoice::find($id);
        $invoice->update($input);

        // Remove old details/charges
        InvoiceDetail::where('invoice_id', $id)->delete();
        AdditionalCharge::where('invoice_id', $id)->delete();

        // Re-add details
        $details = $request->invoice_details ?? [];
        foreach ($details as $detail) {
            $detail['invoice_id'] = $invoice->id;
            $detail['user_id'] = $input['user_id'];
            InvoiceDetail::create($detail);
        }

        // Re-add additional charges
        $charges = $request->invoice_additional_charges ?? [];
        foreach ($charges as $charge) {
            $charge['invoice_id'] = $invoice->id;
            $charge['user_id'] = $input['user_id'];
            AdditionalCharge::create($charge);
        }

        return response($invoice);
    }
}
