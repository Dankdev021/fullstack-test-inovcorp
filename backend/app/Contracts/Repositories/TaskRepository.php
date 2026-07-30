<?php

namespace App\Contracts\Repositories;

use App\DataTransferObjects\CreateTaskData;
use App\DataTransferObjects\TaskFiltersData;
use App\DataTransferObjects\UpdateTaskData;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepository
{
    public function paginateForProject(Project $project, TaskFiltersData $filters): LengthAwarePaginator;

    public function create(Project $project, CreateTaskData $data): Task;

    public function update(Task $task, UpdateTaskData $data): Task;

    public function delete(Task $task): void;
}
