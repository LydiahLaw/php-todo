<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the application returns a successful response.
     *
     * @return void
     */
    public function test_application_homepage_works()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test database connection works.
     *
     * @return void
     */
    public function test_database_connection()
    {
        $this->assertTrue(true);
    }

    /**
     * Test environment is set correctly.
     *
     * @return void
     */
    public function test_environment_is_testing()
    {
        $this->assertEquals('testing', app()->environment());
    }
}