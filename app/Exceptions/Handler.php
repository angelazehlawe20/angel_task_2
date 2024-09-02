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
        if($exception instanceof UnauthorizedHttpException)
        {
            return $this->ErrorResponse('Unauthorized.',401);
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('Resource not found.', 404);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return $this->errorResponse('Too many requests. Please try again later.', 429);
        }

        if ($exception instanceof QueryException) {
            return $this->errorResponse('Database query error. Please check your request and try again.', 500);
        }

        if ($exception instanceof HttpException) {
            return $this->errorResponse('Server error.', $exception->getStatusCode());
        }

        return $this->errorResponse('An unexpected error occurred. Please try again later.', 500);
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
