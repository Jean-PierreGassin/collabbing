<?php

namespace App\Http\Middleware;

use App\Services\Inertia\PagePropsService;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $session = null;

        if ($request->hasSession()) {
            $session = $request->session();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => app(PagePropsService::class)->user($request->user()),
            ],
            'flash' => [
                'status' => fn () => $session?->get('status'),
                'errors' => fn () => $this->sessionErrors($session),
                'repositoryInvitePrompt' => fn () => (bool) $session?->get('repositoryInvitePrompt', false),
                'repositoryAccessPrompt' => fn () => (bool) $session?->get('repositoryAccessPrompt', false),
            ],
            'oldInput' => fn () => (object) Arr::except($session?->getOldInput() ?? [], [
                '_method',
                '_token',
                'current_password',
                'password',
                'password_confirmation',
            ]),
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
                'contact' => route('resources.contact'),
                'pricing' => route('resources.pricing'),
            ],
        ];
    }

    private function sessionErrors(?Session $session): array
    {
        if (! $session) {
            return [];
        }

        $errors = $session->get('errors');

        if (! $errors) {
            return [];
        }

        return $errors->all();
    }
}
