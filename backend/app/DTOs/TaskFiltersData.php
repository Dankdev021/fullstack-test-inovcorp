<?php

namespace App\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class TaskFiltersData
{
    public function __construct(
        public ?TaskStatus $status,
        public ?TaskPriority $priority,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null,
            priority: isset($data['priority']) ? TaskPriority::from($data['priority']) : null,
            perPage: (int) ($data['per_page'] ?? 15),
        );
    }
}
