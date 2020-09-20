<?php

use App\DetectionEvent;
use Illuminate\Database\Seeder;

class DetectionEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DetectionEvent::class, 10)->create();
    }
}
