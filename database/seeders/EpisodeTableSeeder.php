<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Episode;

class EpisodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Episode::truncate();
        Episode::factory()->count(20)->create();
    }
}
