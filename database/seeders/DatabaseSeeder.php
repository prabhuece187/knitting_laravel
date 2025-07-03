<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\State;
use App\Models\Customer;
use App\Models\YarnType;
use App\Models\Item;
use App\Models\Mill;
use App\Models\Inward;
use App\Models\InwardDetail;
use App\Models\Outward;
use App\Models\OutwardDetail;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StateSeeder::class);
        User::factory(2)->create();
        Customer::factory(3)->create();
        YarnType::factory(3)->create();
        Item::factory(3)->create();
        Mill::factory(3)->create();

        Inward::factory(2)->create()->each(function () {
            InwardDetail::factory(3)->create();
        });

        Outward::factory(2)->create()->each(function () {
            OutwardDetail::factory(3)->create();
        });
    }
}
