<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingProductionDetail extends Model
{
    protected $fillable = [
        'knitting_production_id',
        'produced_weight',
        'rolls_count',
        'dia',
        'gsm',
        'user_id',
    ];

    /* =========================
        Relationships
    ========================= */

    public function production()
    {
        return $this->belongsTo(KnittingProduction::class, 'knitting_production_id');
    }
}
