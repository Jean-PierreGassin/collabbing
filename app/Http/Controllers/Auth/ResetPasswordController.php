<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $redirectTo = '/ideas';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('reset');
    }

    public function showResetForm(Request $request, $token = null): Response
    {
        return Inertia::render('Auth/PasswordReset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }
}
