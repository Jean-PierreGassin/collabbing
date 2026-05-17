<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class Handler
 */
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @throws Exception
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     *
     * @throws Exception
     */
    public function render($request, Throwable $exception): Response
    {
        if ($exception instanceof DecryptException) {
            return $this->invalidEncryptedPayloadResponse($request);
        }

        $response = parent::render($request, $exception);

        if (! $request->expectsJson() && $response->getStatusCode() === 419) {
            return redirect()
                ->back()
                ->with('status', 'The page expired. Please try again.');
        }

        return $response;
    }

    private function invalidEncryptedPayloadResponse(Request $request): RedirectResponse|JsonResponse
    {
        $response = $request->expectsJson()
            ? response()->json(['message' => 'The session expired. Please refresh and try again.'], 419)
            : redirect()
                ->to($request->fullUrl())
                ->with('status', 'The session expired. Please refresh and try again.');

        return $response
            ->withoutCookie(config('session.cookie'))
            ->withoutCookie('XSRF-TOKEN');
    }
}
