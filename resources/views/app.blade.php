<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta data-inertia="description" name="description" content="Share early product ideas, find collaborators, and move promising projects toward real work.">
    <meta data-inertia="robots" name="robots" content="index,follow">
    <meta data-inertia="og:title" property="og:title" content="Collabbing">
    <meta data-inertia="og:description" property="og:description" content="Share early product ideas, find collaborators, and move promising projects toward real work.">
    <meta data-inertia="og:type" property="og:type" content="website">
    <meta data-inertia="og:url" property="og:url" content="{{ config('app.url') }}">
    <meta data-inertia="og:site_name" property="og:site_name" content="Collabbing">
    <meta data-inertia="twitter:card" name="twitter:card" content="summary">
    <meta data-inertia="twitter:title" name="twitter:title" content="Collabbing">
    <meta data-inertia="twitter:description" name="twitter:description" content="Share early product ideas, find collaborators, and move promising projects toward real work.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link data-inertia="canonical" rel="canonical" href="{{ config('app.url') }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#f4a51c">
    <title>Collabbing</title>
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebSite",
            "name": "Collabbing",
            "url": "{{ config('app.url') }}",
            "description": "Share early product ideas, find collaborators, and move promising projects toward real work."
        }
    </script>

    @vite(['resources/js/main.ts'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
