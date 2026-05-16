<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Bringing people together to collaborate on projects, one idea at a time">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/js/main.ts'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
