<?php

namespace App\Exceptions;

class TooManyRequestsException extends ApplicationException
{
    public function __construct(string $message = 'Muitas requisições. Tente novamente em breve.')
    {
        parent::__construct($message, 429);
    }
}
