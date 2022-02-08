<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use PDOException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            return errorResponse($exception->status, $exception->getMessage(), $exception->errors());
        }

        if ($exception instanceof AuthenticationException) {
            return $this->send401();
        }

        if ($exception instanceof AuthorizationException) {
            return $this->send403();
        }
        
        if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
            return $this->send404();
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->send405();
        }

        if (method_exists($exception, 'getStatusCode')) {
            if ($exception->getStatusCode() == 404) {
                return $this->send404();
            }

            if ($exception->getStatusCode() == 401) {
                return $this->send401();
            }

            if ($exception->getStatusCode() == 403) {
                return $this->send403();
            }

            if ($exception->getStatusCode() == 404) {
                return $this->send404();
            }
            
            if ($exception->getStatusCode() == 409) {
                return errorResponse(409, __('httpmessages.409'));
            }
        }

        if (!config('app.debug')) {
            return errorResponse(500);
        }

        return parent::render($request, $exception);
    }

    public function send404()
    {
        return errorResponse(404, __('httpmessages.404'));
    }

    public function send405()
    {
        return errorResponse(405, __('httpmessages.405'));
    }

    public function send401()
    {
        return errorResponse(401, __('httpmessages.401'));
    }

    public function send403()
    {
        return errorResponse(403, __('httpmessages.403'));
    }
}
