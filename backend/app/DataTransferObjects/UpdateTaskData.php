<?php

namespace App\DataTransferObjects;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

readonly class UpdateTaskData
{
    public function __construct(
        public ?TaskStatus $status,
        public ?TaskPriority $priority,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: isset($data['status']) ? TaskStatus::from($data['status']) : null,
            priority: isset($data['priority']) ? TaskPriority::from($data['priority']) : null,
        );
    }
}
