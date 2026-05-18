<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/ideas';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('reset');
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse|JsonResponse
    {
        $response = $this->broker()->reset(
            $request->validated(),
            function ($user, string $password): void {
                $this->resetPassword($user, $password);
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return $this->sendResetResponse($request, $response);
        }

        return $this->sendResetFailedResponse($request, $response);
    }

    public function showResetForm(Request $request, ?string $token = null): Response
    {
        return Inertia::render('Auth/PasswordReset', [
            'token' => $token,
            'email' => $request->input('email'),
        ]);
    }
}
