<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\TaskRepository;
use App\DataTransferObjects\CreateTaskData;
use App\DataTransferObjects\TaskFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListProjectTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectTaskController extends Controller
{
    public function __construct(
        private readonly TaskRepository $tasks,
    ) {}

    public function index(
        ListProjectTasksRequest $request,
        Project $project,
    ): AnonymousResourceCollection {
        $tasks = $this->tasks->paginateForProject(
            $project,
            TaskFiltersData::fromArray($request->validated()),
        );

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $this->tasks->create(
            $project,
            CreateTaskData::fromArray($request->validated()),
        );

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }
}
