<?php

use App\DetectionProfile;
use Illuminate\Database\Seeder;

class DetectionProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DetectionProfile::class, 5)->create();
    }
}
