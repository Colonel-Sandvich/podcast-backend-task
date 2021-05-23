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

        $faker = \Faker\Factory::create();
        // Generate some fake data with faker
        for ($i=0; $i < 20; $i++) { 
            Episode::create([
                'download_url' => $faker->url,
                'title' => $faker->sentence,
                'description' => $faker->paragraph,
                'episode_number' => $faker->randomNumber($nbDigits = 4)
            ]);
        }
        //
    }
}
