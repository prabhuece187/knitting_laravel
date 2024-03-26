<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Item;
use App\Models\Outward;
use App\Models\YarnType;

class OutwardDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outward_id',
        'item_id',
        'yarn_type_id',
        'yarn_dia',
        'yarn_gsm',
        'yarn_gauge',
        'outward_qty',
        'outward_weight',
        'deliverd_weight',
        'outward_detail_date',
        'yarn_colour'
    ];

    public function outward(): BelongsTo
    {
        return $this->belongsTo(Outward::class);
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
