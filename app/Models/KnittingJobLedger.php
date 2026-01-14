<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnittingJobLedger extends Model
{
    protected $table = 'knitting_job_ledger';

    protected $fillable = [
        'job_id', 'customer_id', 'type', 'qty', 'remarks'
    ];
}

