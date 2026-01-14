<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingProductionReturn extends Model
{
    protected $fillable = [
        'return_no',
        'return_date',
        'job_card_id',
        'production_id',
        'return_weight',
        'return_reason',
        'rework_required',
        'remarks',
        'user_id',
    ];

    public function jobMaster() {
        return $this->belongsTo(JobMaster::class, 'job_card_id'); // make sure JobMaster exists
    }

    public function production()
    {
        return $this->belongsTo(KnittingProduction::class, 'production_id');
    }

    public function reworks()
    {
        return $this->hasMany(KnittingRework::class, 'production_return_id');
    }
}
