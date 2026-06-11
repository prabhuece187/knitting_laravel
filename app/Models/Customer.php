<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inward;
use App\Models\Outward;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


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

    public function inwards(): HasMany
    {
        return $this->hasMany(Inward::class);
    }

    public function outwards(): HasMany
    {
        return $this->hasMany(Outward::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
