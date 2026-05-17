<?php

namespace App\Http\Middleware;

use App\Services\Inertia\PagePropsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $session = $request->hasSession() ? $request->session() : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => app(PagePropsService::class)->user($request->user()),
            ],
            'flash' => [
                'status' => fn () => $session?->get('status'),
                'errors' => fn () => $session?->get('errors')
                    ? $session->get('errors')->all()
                    : [],
            ],
            'routes' => [
                'home' => route('home'),
                'dashboard' => route('dashboard'),
                'login' => route('login'),
                'logout' => route('logout'),
                'register' => route('register'),
                'passwordRequest' => route('password.request'),
                'passwordEmail' => route('password.email'),
                'passwordReset' => url('/users/password/reset'),
                'ideas' => route('ideas.index'),
                'ideasCreate' => route('ideas.create'),
                'ideasStore' => route('ideas.store'),
                'users' => route('users.index'),
                'feedback' => route('resources.feedback'),
                'pricing' => route('resources.pricing'),
            ],
        ];
    }
}
