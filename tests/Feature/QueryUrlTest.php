<?php

namespace Tests\Feature;

use Symfony\Component\Console\Exception\RuntimeException;
use Tests\TestCase;

class QueryUrlTest extends TestCase
{

    /**
     * Running the command without an url parameter.
     *
     * @return void
     */
    public function test_command_without_a_param()
    {
        $this->expectException(RuntimeException::class);
        $this->artisan('query:url');
    }

    /**
     * Running the command with invalid url parameter.
     *
     * @return void
     */
    public function test_command_invalid_param()
    {
        $this->expectException(RuntimeException::class);
        $this->artisan('query:url ');
    }

    /**
     * Testing if the command ends without error.
     *
     * @return void
     */
    public function test_command_no_errors()
    {
        $this->artisan('query:url https://www.google.com')->assertExitCode(0);
    }
}
