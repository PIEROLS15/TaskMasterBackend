<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase; 

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_tasks_for_authenticated_user()
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_a_task()
    {
        $taskData = [
            'title' => 'Nueva tarea',
            'description' => 'Descripción de la tarea',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Nueva tarea']);
    }

    /** @test */
    public function it_can_show_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $task->id]);
    }

    /** @test */
    public function it_can_update_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $updateData = ['title' => 'Tarea actualizada', 'status' => 'completed'];

        $response = $this->actingAs($this->user)->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Tarea actualizada']);
    }

    /** @test */
    public function it_can_delete_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Tarea eliminada correctamente']);
        
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}