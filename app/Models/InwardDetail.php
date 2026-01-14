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
        'shade',
        'bag_no',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'uom',
        'yarn_gauge',
        'yarn_dia',
        'yarn_gsm',
        'remarks',
        'job_card_id',
    ];

    public function inward(): BelongsTo
    {
        return $this->belongsTo(Inward::class);
    }

    public function item(): BelongsTo
    {
        return $this->BelongsTo(Item::class);
    }

    public function yarnType(): BelongsTo
    {
        return $this->BelongsTo(YarnType::class);
    }
    
    public function jobMaster()
    {
        return $this->belongsTo(JobMaster::class, 'job_card_id');
    }
}
