<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

    class State extends Model
    {
        use HasFactory;

        protected $fillable = ['state_name', 'state_code','user_id'];

        public function customers()
        {
            return $this->hasMany(Customer::class);
        }
    }

