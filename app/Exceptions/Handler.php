<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
            return parent::render($request, $e);
        }

        if ($this->shouldUseFriendlyResponse($request, $e)) {
            $status = $this->statusCodeFor($e);
            $message = $this->friendlyMessageFor($status, $e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], $status);
            }

            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                return back()
                    ->withInput($request->except($this->dontFlash))
                    ->with('error', $message);
            }

            return response()->view('errors.generic', [
                'status' => $status,
                'title' => $this->friendlyTitleFor($status),
                'message' => $message,
            ], $status);
        }

        return parent::render($request, $e);
    }

    private function shouldUseFriendlyResponse($request, Throwable $e): bool
    {
        if ($request->is('livewire/*')) {
            return false;
        }

        return $request->is('__test-friendly-*')
            || $e instanceof HttpExceptionInterface
            || $e instanceof ModelNotFoundException
            || $e instanceof QueryException
            || $e instanceof TokenMismatchException
            || ! app()->runningInConsole();
    }

    private function statusCodeFor(Throwable $e): int
    {
        if ($e instanceof TokenMismatchException) {
            return 419;
        }

        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    private function friendlyTitleFor(int $status): string
    {
        return match ($status) {
            403 => 'Acceso restringido',
            404 => 'No encontramos esta información',
            419 => 'Sesión expirada',
            405 => 'Acción no permitida',
            default => 'No se pudo completar la solicitud',
        };
    }

    private function friendlyMessageFor(int $status, Throwable $e): string
    {
        if ($e instanceof QueryException) {
            return 'No se pudo completar la operación porque ocurrió un problema al consultar o guardar la información. Intenta nuevamente o revisa los datos relacionados.';
        }

        return match ($status) {
            403 => 'No tienes permisos para realizar esta acción.',
            404 => 'La información solicitada no existe o ya no se encuentra disponible.',
            419 => 'Tu sesión expiró. Actualiza la página e intenta nuevamente.',
            405 => 'La acción solicitada no está permitida desde esta pantalla.',
            default => 'No se pudo completar la solicitud. Intenta nuevamente y, si el problema continúa, comunícalo al administrador del sistema.',
        };
    }
}