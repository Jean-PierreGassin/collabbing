<?php

namespace App\Http\Controllers\Auth;

use App\Data\Users\UserRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/ideas';

    public function __construct(private UserService $users)
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('register');
    }

    public function register(RegisterUserRequest $request): RedirectResponse|JsonResponse
    {
        event(new Registered($user = $this->create($request->toData())));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        if ($request->wantsJson()) {
            return new JsonResponse([], 201);
        }

        return redirect($this->redirectPath());
    }

    protected function create(UserRegistrationData $data): User
    {
        return $this->users->create($data);
    }

    public function showRegistrationForm(): Response
    {
        return Inertia::render('Auth/Register');
    }
}
