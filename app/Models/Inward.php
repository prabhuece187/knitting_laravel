<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Customer;
use App\Models\Mill;
use App\Models\InwardDetail;
use App\Models\Outward;

class Inward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'mill_id',
        'inward_no',
        'inward_invoice_no',
        'inward_tin_no',
        'inward_date',
        'total_weight',
        'total_quantity',
        'inward_vehicle_no',
        'status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function inward_details(): HasMany
    {
        return $this->hasMany(InwardDetail::class);
    }

    public function mill(): BelongsTo
    {
        return $this->belongsTo(Mill::class);
    }

    public function outward(): HasMany
    {
        return $this->belongsTo(Outward::class);
    }
}
