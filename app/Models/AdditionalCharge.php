<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'additional_name',
        'additional_amount',
        'tax_applicable',
    ];

    /* ---------------- Relationships ---------------- */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
