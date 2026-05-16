@section('title', 'Edit Profile')
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('errors'))
                    <div class="alert alert-danger">
                        @foreach (session('errors')->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">{{ isset($user) ? 'Edit' : 'Create' }} Profile</div>

                    <div class="card-body">
                        <form action="{{ isset($user) ? route('users.update', $user->username) : route('users.store') }}" method="POST">
                            @csrf
                            @isset($user)
                                @method('PUT')
                            @endisset

                        <div class="form-group row">
                            <div class="col">
                                <label for="first_name">First Name</label>
                                <input id="first_name" name="first_name" type="text" class="form-control"
                                       value="{{ old('first_name', $user->first_name ?? '') }}"
                                       placeholder="John">
                            </div>

                            <div class="col">
                                <label for="last_name">Last Name</label>
                                <input id="last_name" name="last_name" type="text" class="form-control"
                                       value="{{ old('last_name', $user->last_name ?? '') }}"
                                       placeholder="Smith">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-Mail Address</label>
                            <input id="email" name="email" type="email" class="form-control"
                                   value="{{ old('email', $user->email ?? '') }}"
                                   placeholder="john.smith@apples.com">
                        </div>

                        @if (isset($user))
                            <div class="form-group">
                                <h4>Integrations</h4>
                                <p>
                                    <small>
                                        Linking your GitHub account will allow for seamless integration between your
                                        ideas and repositories - view your access to Collabbing
                                        <a href="https://github.com/settings/connections/applications/{{ env('GITHUB_CLIENT_ID') }}">
                                            here
                                        </a>
                                    </small>
                                </p>
                                <a href="{{ $user->github_token ? route('auth.github.revoke') : route('auth.github.login') }}"
                                   class="btn btn-sm {{ $user->github_token ? 'btn-success' : 'btn-outline-success' }}">
                                    <i class="fab fa-github"></i>
                                    {{ $user->github_token ? 'Un-link GitHub' : 'Link GitHub' }}
                                </a>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="bio">Bio (supports markdown)</label>
                            <textarea id="bio" name="bio" class="form-control"
                                      placeholder="Tell us what you're good at and what you enjoy...">{{ old('bio', $user->bio ?? '') }}</textarea>
                        </div>

                        <div class="form-group row">
                            <div class="col">
                                <label for="password">New Password:</label>
                                <input id="password" name="password" type="password" class="form-control">
                            </div>

                            <div class="col">
                                <label for="password_confirmation">Confirm Password:</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Edit Profile' : 'Create Profile' }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
