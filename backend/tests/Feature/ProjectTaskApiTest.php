<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_are_paginated_with_tasks_count(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(3)->for($project)->create();

        $response = $this->getJson('/api/projects?per_page=10');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $project->id)
            ->assertJsonPath('data.0.tasks_count', 3)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_project_can_be_created(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Novo produto',
            'description' => 'Planejamento da nova linha de produto.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Novo produto')
            ->assertJsonPath('data.status', ProjectStatus::Active->value)
            ->assertJsonPath('data.tasks_count', 0);

        $this->assertDatabaseHas('projects', [
            'name' => 'Novo produto',
            'status' => ProjectStatus::Active->value,
        ]);
    }

    public function test_project_creation_requires_valid_data(): void
    {
        $this->postJson('/api/projects', [
            'name' => '',
            'status' => 'invalid',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'status']);
    }

    public function test_project_tasks_can_be_filtered(): void
    {
        $project = Project::factory()->create();

        Task::factory()->for($project)->create([
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::High,
        ]);

        Task::factory()->for($project)->create([
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::Low,
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?status=todo&priority=high",
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', TaskStatus::Todo->value)
            ->assertJsonPath('data.0.priority', TaskPriority::High->value);
    }

    public function test_task_can_be_created_for_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Preparar apresentação',
            'description' => 'Organizar os resultados do projeto.',
            'priority' => TaskPriority::High->value,
            'due_date' => today()->addWeek()->toDateString(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.project_id', $project->id)
            ->assertJsonPath('data.status', TaskStatus::Todo->value)
            ->assertJsonPath('data.priority', TaskPriority::High->value);
    }

    public function test_project_cannot_have_more_than_two_hundred_tasks(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(200)->for($project)->create();

        $this->getJson("/api/projects/{$project->id}/tasks?per_page=200")
            ->assertOk()
            ->assertJsonCount(200, 'data')
            ->assertJsonPath('meta.per_page', 200);

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Tarefa excedente',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('project');

        $this->assertCount(200, $project->tasks()->get());
    }

    public function test_task_status_and_priority_can_be_updated(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Low,
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'status' => TaskStatus::InTesting->value,
            'priority' => TaskPriority::High->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', TaskStatus::InTesting->value)
            ->assertJsonPath('data.priority', TaskPriority::High->value);
    }

    public function test_task_can_be_soft_deleted(): void
    {
        $task = Task::factory()->create();

        $this->deleteJson("/api/tasks/{$task->id}")->assertNoContent();

        $this->assertSoftDeleted($task);
    }

    public function test_overdue_scope_returns_only_tasks_with_past_due_date(): void
    {
        $overdue = Task::factory()->create([
            'due_date' => today()->subDay(),
        ]);

        Task::factory()->create([
            'due_date' => today()->addDay(),
        ]);

        $this->assertTrue(Task::query()->overdue()->get()->contains($overdue));
        $this->assertCount(1, Task::query()->overdue()->get());
    }
}
