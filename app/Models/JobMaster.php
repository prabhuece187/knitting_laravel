<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobMaster extends Model
{
        use HasFactory;

    protected $fillable = [
        'inward_id',
        'customer_id',
        'mill_id',
        'user_id',
        'job_card_no',
        'job_date',
        'approx_job_weight',
        'remarks',
        'expected_delivery_date',
        'status',
    ];

    public function inward(): BelongsTo
    {
        return $this->belongsTo(Inward::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function mill(): BelongsTo
    {
        return $this->belongsTo(Mill::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outwards(): HasMany
    {
        return $this->hasMany(Outward::class);
    }
}
