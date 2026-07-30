<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\TaskRepository;
use App\DataTransferObjects\UpdateTaskData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepository $tasks,
    ) {}

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->tasks->update(
            $task,
            UpdateTaskData::fromArray($request->validated()),
        );

        return (new TaskResource($task))->response();
    }

    public function destroy(Task $task): Response
    {
        $this->tasks->delete($task);

        return response()->noContent();
    }
}
