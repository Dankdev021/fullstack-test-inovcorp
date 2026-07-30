<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\ProjectRepository;
use App\DataTransferObjects\CreateProjectData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListProjectsRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepository $projects,
    ) {}

    public function index(ListProjectsRequest $request): AnonymousResourceCollection
    {
        $projects = $this->projects->paginate((int) $request->validated('per_page', 15));

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projects->create(CreateProjectData::fromArray($request->validated()));

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }
}
