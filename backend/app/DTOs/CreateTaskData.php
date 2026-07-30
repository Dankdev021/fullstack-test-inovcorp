<?php

namespace App\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class CreateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?string $dueDate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            status: TaskStatus::from($data['status']),
            priority: TaskPriority::from($data['priority']),
            dueDate: $data['due_date'] ?? null,
        );
    }
}
