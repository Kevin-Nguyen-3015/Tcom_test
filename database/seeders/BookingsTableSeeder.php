<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BookingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bookings')->insert([
            ['space_id' => 1, 'name' => 'Booking 1', 'start_time' => '09:00:00', 'end_time' => '10:00:00', 'date_booking' => '20240915'],
            ['space_id' => 2, 'name' => 'Booking 2', 'start_time' => '13:00:00', 'end_time' => '15:00:00', 'date_booking' => '20240915'],
            ['space_id' => 3, 'name' => 'Booking 3', 'start_time' => '10:00:00', 'end_time' => '12:00:00', 'date_booking' => '20240916'],
        ]);
    }
}
