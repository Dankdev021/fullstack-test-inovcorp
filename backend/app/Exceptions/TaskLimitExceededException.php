<?php

namespace App\Exceptions;

class TaskLimitExceededException extends ApplicationException
{
    public function __construct()
    {
        $message = 'Este projeto atingiu o limite de 200 tarefas.';

        parent::__construct(
            $message,
            422,
            ['project' => [$message]],
        );
    }
}
