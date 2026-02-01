<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task; // make sure your Task model is in App\Models
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_are_displayed_on_the_dashboard()
    {
        Task::factory()->create(['name' => 'Task 1']);
        Task::factory()->create(['name' => 'Task 2']);
        Task::factory()->create(['name' => 'Task 3']);

        $response = $this->get('/');
        $response->assertSee('Task 1')
                 ->assertSee('Task 2')
                 ->assertSee('Task 3');
    }

    public function test_tasks_can_be_created()
    {
        $response = $this->post('/tasks', ['name' => 'Task 1']);
        $response->assertRedirect('/'); // assuming you redirect after creation

        $this->assertDatabaseHas('tasks', ['name' => 'Task 1']);
    }

    public function test_long_tasks_cant_be_created()
    {
        $response = $this->post('/tasks', ['name' => str_repeat('a', 300)]);
        $response->assertSessionHasErrors(); // expects validation errors
    }
}
