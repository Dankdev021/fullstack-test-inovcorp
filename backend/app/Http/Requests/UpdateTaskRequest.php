<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [Rule::requiredIf(! $this->has('priority')), Rule::enum(TaskStatus::class)],
            'priority' => [Rule::requiredIf(! $this->has('status')), Rule::enum(TaskPriority::class)],
        ];
    }
}
