<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingRework extends Model
{
    protected $fillable = [
        'rework_no',
        'rework_date',
        'production_return_id',
        'job_card_id',
        'rework_weight',
        'remarks',
        'user_id'
    ];

    public function productionReturn()
    {
        return $this->belongsTo(KnittingProductionReturn::class,'production_return_id');
    }

     public function jobMaster()
    {
        return $this->belongsTo(JobMaster::class, 'job_card_id');
    }
}
