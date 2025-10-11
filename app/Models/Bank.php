<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'bank_name',
        'branch_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'bank_city',
        'bank_state',
        'bank_email',
        'bank_mobile',
        'bank_address',
        'is_default'
    ];

      // A bank belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A bank can be linked to many invoices
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
