<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingMachine extends Model
{
    protected $fillable = [
        'user_id',
        'machine_no',
        'machine_name',
        'brand',
        'model',
        'dia',
        'gauge',
        'feeder',
        'status',
        'remarks',
    ];
}