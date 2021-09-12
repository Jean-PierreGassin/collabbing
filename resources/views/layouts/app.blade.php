<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO things -->
    <meta name="description" content="Bringing people together to collaborate on projects, one idea at a time">
    <meta name="keywords" content="collaborate,collaboration,ideas,development,software,programming">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.9/js/all.js"
            integrity="sha384-8iPTk2s/jMVj81dnzb/iFR2sdA7u06vHJyyLlAd4snFpCl/SnyUjRrbdJsw1pGIl"
            crossorigin="anonymous"></script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-117140602-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag () {
        dataLayer.push(arguments);
      }

      gtag('js', new Date());

      gtag('config', 'UA-117140602-1');
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app" class="mb-5" style="margin-bottom: 10rem !important">
    @include('components.navigation')

    <main class="py-4">
        @yield('content')
    </main>
</div>

<div class="fixed-bottom bg-light">
    <footer class="footer mt-4 border-top border-dark">
        <div class="row m-2">
            <div class="col-6 col-md-6">
                <h5>Resources</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="text-muted" href="{{ route('resources.feedback') }}">Feedback</a></li>
                    {{--                    <li><a class="text-muted" href="{{ route('resources.pricing') }}">Pricing</a></li>--}}
                </ul>
            </div>
            <div class="col-6 col-md-6 text-right">
                <small class="d-block mb-3 text-muted">
                    <a href="https://github.com/Jean-PierreGassin">Created & owned by Jean-Pierre Gassin</a>
                </small>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
