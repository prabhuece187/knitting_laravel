<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $states = [
            ['state_name' => 'Jammu and Kashmir', 'state_code' => 1],
            ['state_name' => 'Himachal Pradesh', 'state_code' => 2],
            ['state_name' => 'Punjab', 'state_code' => 3],
            ['state_name' => 'Chandigarh', 'state_code' => 4],
            ['state_name' => 'Uttarakhand', 'state_code' => 5],
            ['state_name' => 'Haryana', 'state_code' => 6],
            ['state_name' => 'Delhi', 'state_code' => 7],
            ['state_name' => 'Rajasthan', 'state_code' => 8],
            ['state_name' => 'Uttar Pradesh', 'state_code' => 9],
            ['state_name' => 'Bihar', 'state_code' => 10],
            ['state_name' => 'Sikkim', 'state_code' => 11],
            ['state_name' => 'Arunachal Pradesh', 'state_code' => 12],
            ['state_name' => 'Nagaland', 'state_code' => 13],
            ['state_name' => 'Manipur', 'state_code' => 14],
            ['state_name' => 'Mizoram', 'state_code' => 15],
            ['state_name' => 'Tripura', 'state_code' => 16],
            ['state_name' => 'Meghalaya', 'state_code' => 17],
            ['state_name' => 'Assam', 'state_code' => 18],
            ['state_name' => 'West Bengal', 'state_code' => 19],
            ['state_name' => 'Jharkhand', 'state_code' => 20],
            ['state_name' => 'Odisha', 'state_code' => 21],
            ['state_name' => 'Chhattisgarh', 'state_code' => 22],
            ['state_name' => 'Madhya Pradesh', 'state_code' => 23],
            ['state_name' => 'Gujarat', 'state_code' => 24],
            ['state_name' => 'Daman and Diu', 'state_code' => 25],
            ['state_name' => 'Dadra and Nagar Haveli', 'state_code' => 26],
            ['state_name' => 'Maharashtra', 'state_code' => 27],
            ['state_name' => 'Andhra Pradesh (Old)', 'state_code' => 28],
            ['state_name' => 'Karnataka', 'state_code' => 29],
            ['state_name' => 'Goa', 'state_code' => 30],
            ['state_name' => 'Lakshadweep', 'state_code' => 31],
            ['state_name' => 'Kerala', 'state_code' => 32],
            ['state_name' => 'Tamil Nadu', 'state_code' => 33],
            ['state_name' => 'Puducherry', 'state_code' => 34],
            ['state_name' => 'Andaman and Nicobar Islands', 'state_code' => 35],
            ['state_name' => 'Telangana', 'state_code' => 36],
            ['state_name' => 'Andhra Pradesh (New)', 'state_code' => 37],
            ['state_name' => 'Ladakh', 'state_code' => 38],
        ];

        DB::table('states')->insert($states);
    }
}
                                                                                                                                                                                                                                                                                                                                                                     