<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Bringing people together to collaborate on projects, one idea at a time">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Collabbing') }} App</title>

    @vite(['resources/js/main.ts'])
</head>
<body>
    <div id="vue-app"></div>

    <script>
        window.__COLLABBING__ = {{ Illuminate\Support\Js::from([
            'appName' => config('app.name', 'Collabbing'),
            'user' => auth()->user()?->only(['id', 'name', 'username', 'email']),
            'routes' => [
                'home' => route('home'),
                'app' => route('app'),
                'appIdeas' => url('/app/ideas'),
                'appDashboard' => url('/app/dashboard'),
                'appResources' => url('/app/resources'),
                'classicIdeas' => route('ideas.index'),
                'classicDashboard' => route('dashboard'),
                'login' => route('login'),
                'register' => route('register'),
                'feedback' => route('resources.feedback'),
                'pricing' => route('resources.pricing'),
            ],
        ]) }};
    </script>
</body>
</html>
