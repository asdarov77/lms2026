<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class HandleApiErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            // Convert non-JSON responses to JSON for API routes if needed
            if ($this->shouldBeJson($request) && !$this->isJsonResponse($response)) {
                return $this->formatResponse($response);
            }

            return $response;
        } catch (Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Handle the exception and return a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleException(Request $request, Throwable $e)
    {
        Log::error('API Error: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $status = $this->getStatusCode($e);
        $message = $this->getErrorMessage($e);
        $details = $this->getErrorDetails($e);

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'details' => $details,
            'code' => $status
        ], $status);
    }

    /**
     * Determine if the request expects a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldBeJson(Request $request)
    {
        return $request->expectsJson() || 
               $request->is('api/*') || 
               $request->is('*/json');
    }

    /**
     * Determine if the response is already a JSON response.
     *
     * @param  mixed  $response
     * @return bool
     */
    protected function isJsonResponse($response)
    {
        if ($response instanceof JsonResponse) {
            return true;
        }

        if ($response instanceof Response && $response->headers->get('Content-Type') === 'application/json') {
            return true;
        }

        return false;
    }

    /**
     * Format a non-JSON response to JSON.
     *
     * @param  mixed  $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($response)
    {
        $status = $response->getStatusCode();
        
        if ($status >= 400) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred.',
                'code' => $status
            ], $status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $response->getContent(),
            'code' => $status
        ], $status);
    }

    /**
     * Get the HTTP status code for the exception.
     *
     * @param  \Throwable  $e
     * @return int
     */
    protected function getStatusCode(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY;
        }
        
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return SymfonyResponse::HTTP_NOT_FOUND;
        }
        
        if ($e instanceof AuthenticationException) {
            return SymfonyResponse::HTTP_UNAUTHORIZED;
        }
        
        if ($e instanceof AuthorizationException) {
            return SymfonyResponse::HTTP_FORBIDDEN;
        }
        
        if ($e instanceof MethodNotAllowedHttpException) {
            return SymfonyResponse::HTTP_METHOD_NOT_ALLOWED;
        }
        
        return SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Get the error message for the exception
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function getErrorMessage(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return 'Проверьте правильность заполнения полей.';
        }
        
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 'Запрашиваемый ресурс не найден.';
        }
        
        if ($e instanceof AuthenticationException) {
            return 'Необходима авторизация для выполнения этого действия.';
        }
        
        if ($e instanceof AuthorizationException) {
            return 'У вас нет прав для выполнения этого действия.';
        }
        
        if ($e instanceof MethodNotAllowedHttpException) {
            return 'Метод запроса не поддерживается для этого ресурса.';
        }

        return app()->environment('production')
            ? 'Произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.'
            : $e->getMessage() ?: 'Произошла неизвестная ошибка.';
    }

    /**
     * Get detailed error information
     *
     * @param  \Throwable  $e
     * @return array|null
     */
    protected function getErrorDetails(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return ['validation' => $e->validator->errors()->toArray()];
        }
        
        if (app()->environment('production')) {
            return null;
        }
        
        return [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ];
    }
} 