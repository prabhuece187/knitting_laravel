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
        'inward_date',
        'supplier_invoice_no',
        'vehicle_no',
        'total_weight',
        'lot_no',
        'no_of_bags',
        'remarks',
        'received_by',
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

    public function outwards(): HasMany
    {
        return $this->hasMany(Outward::class);
    }

    public function jobMasters(): HasMany
    {
        return $this->hasMany(JobMaster::class);
    }

    
}
