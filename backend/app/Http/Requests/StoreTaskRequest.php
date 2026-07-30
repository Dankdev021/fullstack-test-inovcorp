<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $project = $this->route('project');

                if ($project instanceof Project && $project->tasks()->count() >= 200) {
                    $validator->errors()->add('project', 'Este projeto atingiu o limite de 200 tarefas.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', TaskStatus::Todo->value),
            'priority' => $this->input('priority', TaskPriority::Medium->value),
        ]);
    }
}
