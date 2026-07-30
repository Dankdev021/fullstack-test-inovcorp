<?php

namespace App\Contracts\Repositories;

use App\DTOs\CreateProjectData;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepository
{
    public function paginate(int $perPage): LengthAwarePaginator;

    public function create(CreateProjectData $data): Project;
}
