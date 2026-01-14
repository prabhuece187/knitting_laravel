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
        'job_card_id',
        'outward_id',
        'item_id',
        'yarn_type_id',
        'shade',
        'lot_no',
        'bag_no',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'fabric_weight',
        'uom',
        'remarks'
    ];

    public function outward(): BelongsTo
    {
        return $this->belongsTo(Outward::class);
    }

    public function item(): BelongsTo
    {
        return $this->BelongsTo(Item::class);
    }

    public function yarnType(): BelongsTo
    {
        return $this->BelongsTo(YarnType::class);
    }
}
