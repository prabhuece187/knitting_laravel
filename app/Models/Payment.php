<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_type',
        'reference_no',
        'bank_name',
        'cheque_date',
        'note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'payment_invoices', 'payment_id', 'invoice_id')
        ->withPivot([
            'invoice_amount',
            'paid_before',
            'pay_now',
        ]);
    }
}
