<nav class="navbar navbar-expand-md navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ route('ideas.index') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            @auth
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item {{ Route::currentRouteName() === 'dashboard' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-success" href="{{ route('ideas.create') }}">Create an Idea</a>
                    </li>
                </ul>
            @endauth

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto order-3">
                <!-- Authentication Links -->
                @guest
                    <li><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                    <li><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->first_name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('users.update', Auth::user()->username) }}">
                                View Profile
                            </a>

                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="
                                event.preventDefault();
                                document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>

            <div class="col-12 mt-3 mt-sm-0 col-md-6 ml-auto order-sm-2">
                {!! Form::open(['route' => 'ideas.index', 'method' => 'GET']) !!}

                <div class="input-group">
                    {!! Form::search('search', null, [
                        'class' => 'form-control py-2 bg-light text-white border border-secondary border-right-0',
                        'placeholder' => 'a robot that sings karaoke...' ,
                        'aria-describedby' => 'contentHelp',
                    ]) !!}
                    {!! Form::submit('Search Ideas', ['class' => 'btn btn-secondary rounded-0']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</nav>