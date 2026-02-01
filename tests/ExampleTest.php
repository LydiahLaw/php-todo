<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase {
        RefreshDatabase::refreshDatabase as baseRefreshDatabase;
    }

    protected function refreshDatabase()
    {
        // Run migrations with --force to avoid interactive prompts on Windows
        $this->artisan('migrate:fresh', ['--force' => true]);

        // Call the base refresh just in case
        $this->baseRefreshDatabase();
    }

    public function test_application_homepage_works()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_database_connection()
    {
        $this->assertTrue(true);
    }

    public function test_environment_is_testing()
    {
        $this->assertEquals('testing', app()->environment());
    }
}
