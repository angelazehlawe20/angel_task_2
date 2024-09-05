<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Traits\ApiTrait;

class Handler extends ExceptionHandler
{
    use ApiTrait;

    public function render($request, Throwable $exception)
{
    if ($exception instanceof UnauthorizedHttpException) {
        return $this->ErrorResponse('Unauthorized.', 401);
    }

    if ($exception instanceof NotFoundHttpException) {
        return $this->ErrorResponse('Resource not found.', 404);
    }

    if ($exception instanceof AccessDeniedHttpException) {
        return $this->ErrorResponse('Forbidden.', 403);
    }

    if ($exception instanceof TooManyRequestsHttpException) {
        return $this->ErrorResponse('Too many requests. Please try again later.', 429);
    }

    if ($exception instanceof ValidationException) {
        return $this->ErrorResponse('Validation error. Please check the input and try again.', 422, $exception->errors());
    }

    if ($exception instanceof QueryException) {
        return $this->ErrorResponse('Database query error. Please check your request and try again.', 500);
    }

    if ($exception instanceof HttpException) {
        return $this->ErrorResponse('Server error.', $exception->getStatusCode());
    }

    return parent::render($request, $exception); 
}




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
}
