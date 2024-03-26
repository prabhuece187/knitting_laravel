<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\InwardDetail;
use App\Models\OutwardDetail;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
    ];

    public function inward_detail(): HasOne
    {
        return $this->hasOne(InwardDetail::class);
    }

    public function outward_detail(): HasOne
    {
        return $this->hasOne(OutwardDetail::class);
    }
}
