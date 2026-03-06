<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Invoice;

class PaymentController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = Payment::with([
            'invoices' => function ($q) {
                $q->select(
                    'invoices.id',
                    'invoices.customer_id',
                    'invoices.invoice_number',
                    'invoices.invoice_total',
                    'invoices.balance_amount'
                )
                ->with('customer')
                ->withPivot(['invoice_amount', 'paid_before', 'pay_now']);
            }
        ])->select('payments.*');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                $q->where('payments.id', 'like', "%{$search}%")
                ->orWhere('payments.payment_date', 'like', "%{$search}%")
                ->orWhere('payments.payment_type', 'like', "%{$search}%")
                ->orWhere('payments.amount', 'like', "%{$search}%")
                ->orWhere('payments.reference_no', 'like', "%{$search}%");

                $q->orWhereHas('invoices.customer', function ($qc) use ($search) {
                    $qc->where('customer_name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderBy('payments.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }


    /* -----------------------------------------
        CREATE PAYMENT
    ------------------------------------------*/

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'payment_date'    => 'required|date',
            'payment_type'    => 'required|string',
            'reference_no'    => 'nullable|string',
            'note'            => 'nullable|string',

            'payment_details' => 'required|array|min:1',

            'payment_details.*.invoice_id' => 'required|exists:invoices,id',
            'payment_details.*.amount'     => 'required|numeric|min:0.01',

            'total_amount'    => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($validated) {

            // Create payment
            $payment = Payment::create([
                'customer_id'  => $validated['customer_id'],
                'user_id'      => Auth::id(), // ✅ Auth user
                'payment_date' => $validated['payment_date'],
                'amount'       => $validated['total_amount'],
                'payment_type' => $validated['payment_type'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note'         => $validated['note'] ?? null,
            ]);

            $responseInvoices = [];

            foreach ($validated['payment_details'] as $item) {

                $invoice = Invoice::findOrFail($item['invoice_id']);

                $previousPaid = $invoice->amount_received;
                $payNow       = $item['amount'];

                PaymentInvoice::create([
                    'payment_id'     => $payment->id,
                    'invoice_id'     => $invoice->id,
                    'invoice_amount' => $invoice->invoice_total,
                    'paid_before'    => $previousPaid,
                    'pay_now'        => $payNow,
                ]);

                $responseInvoices[] = [
                    'invoice_id'      => $invoice->id,
                    'invoice_amount'  => $invoice->invoice_total,
                    'total_paid'      => $invoice->amount_received,
                    'current_balance' => $invoice->invoice_total - $invoice->amount_received,
                ];
            }

            return response()->json([
                'payment'  => $payment,
                'invoices' => $responseInvoices
            ]);
        });
    }


    /* -----------------------------------------
        DELETE PAYMENT
    ------------------------------------------*/

    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->invoices()->detach();

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }


    /* -----------------------------------------
        GET CUSTOMER INVOICES
    ------------------------------------------*/

    public function getInvoicesByCustomer($customerId)
    {
        $invoices = Invoice::where('customer_id', $customerId)
            ->with(['paymentsSettlements'])
            ->orderBy('invoice_date', 'asc')
            ->get()
            ->filter(function ($invoice) {
                return $invoice->current_balance > 0;
            })
            ->values()
            ->map(function ($invoice) {
                return [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date'   => $invoice->invoice_date,
                    'invoice_total'  => $invoice->invoice_total,
                    'total_paid'     => $invoice->total_paid,
                    'pending_amount' => $invoice->current_balance,
                ];
            });

        return response()->json([
            'customer_id' => $customerId,
            'invoices'    => $invoices
        ]);
    }

}