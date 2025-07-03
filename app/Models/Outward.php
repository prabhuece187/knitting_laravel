<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Customer;
use App\Models\OutwardDetail;
use App\Models\Mill;
use App\Models\Inward;

class Outward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'mill_id',
        'outward_no',
        'inward_id',
        'outward_invoice_no',
        'outward_tin_no',
        'outward_date',
        'total_weight',
        'total_quantity',
        'outward_vehicle_no',
        'status',
        'yarn_send',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function outward_details(): HasMany
    {
        return $this->hasMany(OutwardDetail::class);
    }

    public function mill(): BelongsTo
    {
        return $this->belongsTo(Mill::class);
    }

    public function inward(): BelongsTo
    {
        return $this->belongsTo(Inward::class);
    }
}
