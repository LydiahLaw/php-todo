<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task; // Make sure Task model namespace is correct
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tasks_are_displayed_on_the_dashboard()
    {
        Task::factory()->create(['name' => 'Task 1']);
        Task::factory()->create(['name' => 'Task 2']);
        Task::factory()->create(['name' => 'Task 3']);

        $this->get('/')
             ->assertSee('Task 1')
             ->assertSee('Task 2')
             ->assertSee('Task 3');
    }

    public function test_tasks_can_be_created()
    {
        $this->get('/')->assertDontSee('Task 1');

        $this->post('/', ['name' => 'Task 1'])
             ->assertSee('Task 1');
    }

    public function test_long_tasks_cant_be_created()
    {
        $this->post('/', ['name' => str()->random(300)])
             ->assertSee('Whoops!');
    }
}
