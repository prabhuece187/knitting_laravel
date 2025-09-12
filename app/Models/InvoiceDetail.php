<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'item_id',
        'item_description',
        'hsn_code',
        'quantity',
        'price',
        'item_discount_per',
        'item_discount_amount',
        'item_tax_per',       
        'item_tax_amount',
        'amount',
    ];

    /* ---------------- Relationships ---------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
