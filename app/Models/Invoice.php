<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'bank_id',
        'invoice_number',
        'invoice_type',
        'invoice_date',
        'payment_terms',
        'due_date',
        'invoice_notes',
        'invoice_terms',
        'invoice_subtotal',
        'invoice_total_quantity',   
        'invoice_taxable_value',
        'invoice_total',
        'invoice_cgst',
        'invoice_sgst',
        'invoice_igst',
        'bill_discount_per',
        'bill_discount_amount',
        'bill_discount_type',
        'amount_received',
        'balance_amount',
        'round_off',
        'amount_received_type',
        'fully_paid',
        'subtotal_discount',
        'subtotal_tax',
        'round_off_amount',
        'round_off_type'
    ];

    protected $appends = ['total_paid', 'current_balance'];

    /* ---------------- Relationships ---------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function additionalCharges()
    {
        return $this->hasMany(AdditionalCharge::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function taxes()
    {
        return $this->hasMany(InvoiceTax::class);
    }

    // inside Invoice class

    public function paymentsSettlements()
    {
        // payment_invoices belonging to this invoice
        return $this->hasMany(PaymentInvoice::class, 'invoice_id');
    }

    // public function payments()
    // {
    //     return $this->belongsToMany(Payment::class, 'payment_invoices', 'invoice_id', 'payment_id')
    //                 ->withPivot(['invoice_amount','paid_before','pay_now'])
    //                 ->withTimestamps();
    // }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_invoices', 'invoice_id', 'payment_id')
            ->withPivot([
                'invoice_amount',
                'paid_before',
                'pay_now'
            ]);
    }

    /**
     * Calculate total paid for this invoice (initial amount_received + settlements)
     */
    public function getTotalPaidAttribute()
    {
        // initial amount received at invoice creation
        $initial = $this->amount_received ?? 0;

        // sum of pay_now from payment_invoices
        $settlements = $this->paymentsSettlements()->sum('pay_now');

        return (float) $initial + (float) $settlements;
    }

    /**
     * Current balance (recalc on demand)
     */
    public function getCurrentBalanceAttribute()
    {
        $totalPaid = $this->total_paid; // uses accessor above
        return (float) $this->invoice_total - $totalPaid;
    }

}
