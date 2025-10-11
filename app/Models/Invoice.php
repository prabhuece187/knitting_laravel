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
    ];

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
}
