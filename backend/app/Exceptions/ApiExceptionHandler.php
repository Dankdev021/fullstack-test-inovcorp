<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ApplicationException $exception, Request $request) {
            if (! self::isApiRequest($request)) {
                return null;
            }

            return $exception->toResponse();
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! self::isApiRequest($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Os dados enviados são inválidos.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! self::isApiRequest($request)) {
                return null;
            }

            return (new ResourceNotFoundException)->toResponse();
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! self::isApiRequest($request)) {
                return null;
            }

            $previous = $exception->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                return (new ResourceNotFoundException)->toResponse();
            }

            return (new ResourceNotFoundException('Rota não encontrada.'))->toResponse();
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            if (! self::isApiRequest($request)) {
                return null;
            }

            return (new TooManyRequestsException)->toResponse();
        });
    }

    private static function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
