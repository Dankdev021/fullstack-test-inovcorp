<?php

namespace App\DataTransferObjects;

use App\Enums\ProjectStatus;

readonly class CreateProjectData
{
    public function __construct(
        public string $name,
        public string $description,
        public ProjectStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            status: ProjectStatus::from($data['status']),
        );
    }
}
