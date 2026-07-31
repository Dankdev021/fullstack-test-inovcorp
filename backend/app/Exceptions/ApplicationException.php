<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApplicationException extends Exception
{
    public function __construct(
        string $message,
        protected readonly int $status = 400,
        protected readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function toResponse(): JsonResponse
    {
        $payload = ['message' => $this->getMessage()];

        if ($this->errors !== []) {
            $payload['errors'] = $this->errors;
        }

        return response()->json($payload, $this->status);
    }
}
