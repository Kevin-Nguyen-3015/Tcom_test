<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('spaces')->insert([
            ['room_id' => 1, 'name' => 'Space A'],
            ['room_id' => 1, 'name' => 'Space B'],
            ['room_id' => 2, 'name' => 'Space C'],
            ['room_id' => 3, 'name' => 'Space D'],
        ]);
    }
}
