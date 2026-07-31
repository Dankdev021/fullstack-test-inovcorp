<?php

namespace App\Exceptions;

class ResourceNotFoundException extends ApplicationException
{
    public function __construct(string $message = 'Recurso não encontrado.')
    {
        parent::__construct($message, 404);
    }
}
