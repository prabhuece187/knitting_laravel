<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inward;
use App\Models\Outward;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Mill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mill_name',
        'mobile_number',
        'address',
        'description',
    ];

    public function inward(): HasOne
    {
        return $this->hasOne(Inward::class);
    }

    public function outward(): HasOne
    {
        return $this->hasOne(Outward::class);
    }
}
