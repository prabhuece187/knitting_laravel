<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InwardDetail;
use App\Models\OutwardDetail;

class YarnType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'yarn_type',
        'yarn_gauge',
        'yarn_dia',
        'yarn_gsm',
    ];

    public function inward_details()
    {
        return $this->hasMany(InwardDetail::class);
    }

    public function outward_details()
    {
        return $this->hasMany(OutwardDetail::class);
    }
}
