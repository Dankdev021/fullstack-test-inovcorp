<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', TaskStatus::Todo->value),
            'priority' => $this->input('priority', TaskPriority::Medium->value),
        ]);
    }
}
