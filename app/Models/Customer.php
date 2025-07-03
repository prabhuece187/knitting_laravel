<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inward;
use App\Models\Outward;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'state_id',
        'customer_name',
        'customer_gst_no',
        'customer_mobile',
        'customer_email',
        'customer_address',
    ];

    public function inward(): HasOne
    {
        return $this->hasOne(Inward::class);
    }

    public function outward(): HasOne
    {
        return $this->hasOne(Outward::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
