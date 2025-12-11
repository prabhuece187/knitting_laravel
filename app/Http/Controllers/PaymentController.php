<?php   
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentInvoice;
use App\Models\Invoice;
use Auth;
use DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with([
            'invoices' => function ($q) {
                $q->select('invoices.id', 'invoices.customer_id', 'invoices.invoice_number', 'invoices.invoice_total', 'invoices.balance_amount')
                ->with('customer')   // ← load customer here
                ->withPivot(['invoice_amount', 'paid_before', 'pay_now']);
            }
        ])->latest()->get();

        return response()->json($payments);
    }

    /* -----------------------------------------
        CREATE PAYMENT
    ------------------------------------------*/
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'payment_date' => 'required|date',
    //         'amount'       => 'required|numeric|min:0.01',
    //         'payment_type' => 'required',
    //         'reference_no' => 'nullable|string',
    //         'note'         => 'nullable|string',
    //     ]);

    //     return DB::transaction(function () use ($validated) {

    //         $invoice = Invoice::findOrFail($validated['invoice_id']);

    //         $payment = Payment::create([
    //             'invoice_id'   => $invoice->id,
    //             'customer_id'  => $invoice->customer_id,
    //             'user_id'      => Auth::id(),
    //             'payment_date' => $validated['payment_date'],
    //             'amount'       => $validated['amount'],
    //             'payment_type' => $validated['payment_type'],
    //             'reference_no' => $validated['reference_no'] ?? null,
    //             'note'         => $validated['note'] ?? null,
    //         ]);

    //         return response()->json([
    //             'payment' => $payment,
    //             'total_paid' => $invoice->fresh()->total_paid,
    //             'current_balance' => $invoice->fresh()->current_balance,
    //         ]);
    //     });
    // }

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

            // 1️⃣ Create main payment entry
            $payment = Payment::create([
                'customer_id'  => $validated['customer_id'],
                'user_id'      => Auth::id(),
                'payment_date' => $validated['payment_date'],
                'amount'       => $validated['total_amount'],
                'payment_type' => $validated['payment_type'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note'         => $validated['note'] ?? null,
            ]);

            $responseInvoices = [];

            // 2️⃣ Loop through each invoice in payment_details
            foreach ($validated['payment_details'] as $item) {

                $invoice = Invoice::findOrFail($item['invoice_id']);

                $previousPaid = $invoice->amount_received;
                $payNow       = $item['amount'];

                // 3️⃣ Create payment_invoice record
                PaymentInvoice::create([
                    'payment_id'   => $payment->id,
                    'invoice_id'   => $invoice->id,
                    'invoice_amount' => $invoice->invoice_total,
                    'paid_before'  => $previousPaid,
                    'pay_now'      => $payNow,
                ]);

                // Prepare response
                $responseInvoices[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_amount'    => $invoice->invoice_total,
                    'total_paid'        => $invoice->amount_received,
                    'current_balance'   => $invoice->invoice_total - $invoice->amount_received,
                ];
            }

            // 5️⃣ Return final updated data
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

        // Delete related pivot records
        $payment->invoices()->detach(); // removes entries from payment_invoices

        // Delete the payment
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }

    // public function getInvoicesByCustomer($customerId)
    // {
    //     $invoices = Invoice::where('customer_id', $customerId)
    //         ->with(['paymentsSettlements'])
    //         ->orderBy('invoice_date', 'asc')
    //         ->get()
    //         ->map(function ($invoice) {
    //             return [
    //                 'id' => $invoice->id,
    //                 'invoice_number' => $invoice->invoice_number,
    //                 'invoice_date' => $invoice->invoice_date,
    //                 'invoice_total' => $invoice->invoice_total,
    //                 'total_paid' => $invoice->total_paid,
    //                 'pending_amount' => $invoice->current_balance,
    //             ];
    //         });

    //     return response()->json([
    //         'customer_id' => $customerId,
    //         'invoices' => $invoices
    //     ]);
    // }

    public function getInvoicesByCustomer($customerId)
    {
        $invoices = Invoice::where('customer_id', $customerId)
            ->with(['paymentsSettlements'])
            ->orderBy('invoice_date', 'asc')
            ->get()
            ->filter(function ($invoice) {
                return $invoice->current_balance > 0; // ONLY pending invoices
            })
            ->values() // reset index
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date,
                    'invoice_total' => $invoice->invoice_total,
                    'total_paid' => $invoice->total_paid,
                    'pending_amount' => $invoice->current_balance,
                ];
            });

        return response()->json([
            'customer_id' => $customerId,
            'invoices' => $invoices
        ]);
    }

}
