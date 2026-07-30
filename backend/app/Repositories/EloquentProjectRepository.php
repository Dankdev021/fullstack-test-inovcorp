<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProjectRepository;
use App\DTOs\CreateProjectData;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProjectRepository implements ProjectRepository
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Project::query()
            ->withCount('tasks')
            ->latest()
            ->paginate($perPage);
    }

    public function create(CreateProjectData $data): Project
    {
        return Project::query()->create([
            'name' => $data->name,
            'description' => $data->description,
            'status' => $data->status,
        ])->loadCount('tasks');
    }
}
