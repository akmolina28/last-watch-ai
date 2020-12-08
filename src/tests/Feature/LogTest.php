<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LogTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    function application_can_write_to_log_file()
    {

        Log::info("test passed");
    }
}
