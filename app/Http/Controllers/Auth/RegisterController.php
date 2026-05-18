<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected $redirectTo = '/ideas';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('register');
    }

    protected function validator(array $data): Validator
    {
        return ValidatorFacade::make(
            $data,
            [
                'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:users,username'],
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'string', 'confirmed', 'max:128', Password::min(12)->letters()->numbers()],
            ]
        );
    }

    protected function create(array $data): User
    {
        return User::create(
            [
                'username' => $data['username'],
                'first_name' => ucwords($data['first_name']),
                'last_name' => ucwords($data['last_name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]
        );
    }

    public function showRegistrationForm(): Response
    {
        return Inertia::render('Auth/Register');
    }
}
