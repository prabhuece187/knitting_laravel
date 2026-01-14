<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingProduction extends Model
{
    protected $fillable = [
        'production_date',
        'production_no',
        'user_id',
        'job_card_id',
        'machine_id',
        'shift',
        'operator_name',
        'remarks',
    ];

    /* =========================
        Relationships
    ========================= */

    public function jobMaster()
    {
        return $this->belongsTo(JobMaster::class, 'job_card_id');
    }

    public function machine()
    {
        return $this->belongsTo(KnittingMachine::class);
    }

    public function details()
    {
        return $this->hasMany(KnittingProductionDetail::class);
    }
}
