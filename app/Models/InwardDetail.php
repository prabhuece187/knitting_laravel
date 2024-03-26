<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Item;
use App\Models\YarnType;

class InwardDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'inward_id',
        'item_id',
        'yarn_type_id',
        'yarn_dia',
        'yarn_gsm',
        'yarn_gauge',
        'inward_qty',
        'inward_weight',
        'inward_detail_date',
        'yarn_colour'
    ];

    public function inward(): BelongsTo
    {
        return $this->belongsTo(Inward::class);
    }

    public function item(): BelongsTo
    {
        return $this->BelongsTo(Item::class);
    }

    public function yarn_type(): BelongsTo
    {
        return $this->BelongsTo(YarnType::class);
    }
}
