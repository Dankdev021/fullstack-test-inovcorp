<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepository;
use App\DTOs\CreateTaskData;
use App\DTOs\TaskFiltersData;
use App\DTOs\UpdateTaskData;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepository
{
    public function paginateForProject(Project $project, TaskFiltersData $filters): LengthAwarePaginator
    {
        return $project->tasks()
            ->when($filters->status, fn ($query) => $query->where('status', $filters->status->value))
            ->when($filters->priority, fn ($query) => $query->where('priority', $filters->priority->value))
            ->latest()
            ->paginate($filters->perPage);
    }

    public function create(Project $project, CreateTaskData $data): Task
    {
        return $project->tasks()->create([
            'title' => $data->title,
            'description' => $data->description,
            'status' => $data->status,
            'priority' => $data->priority,
            'due_date' => $data->dueDate,
        ]);
    }

    public function update(Task $task, UpdateTaskData $data): Task
    {
        $attributes = array_filter([
            'status' => $data->status,
            'priority' => $data->priority,
        ], fn ($value) => $value !== null);

        $task->update($attributes);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
