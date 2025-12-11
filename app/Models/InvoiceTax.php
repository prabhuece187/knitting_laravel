<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'tax_type',
        'tax_rate',
        'tax_amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);    
    }
}
