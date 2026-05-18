<?php

namespace App\Exceptions;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

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
        if ($request->expectsJson()) {
            $response = response()->json(['message' => 'The session expired. Please refresh and try again.'], 419);
        } else {
            $response = redirect()
                ->to($request->fullUrl())
                ->with('status', 'The session expired. Please refresh and try again.');
        }

        return $response
            ->withoutCookie(config('session.cookie'))
            ->withoutCookie('XSRF-TOKEN');
    }
}
