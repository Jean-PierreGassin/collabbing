@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">
                                {{ ucwords($user->first_name) . ' ' . ucwords($user->last_name) }}
                            </div>

                            <div class="col-sm text-sm-right">
                                @auth
                                    @if (Auth::user()->id === $user->id)
                                        <a class="btn btn-dark btn-sm" href="{{ route('users.edit', $user->username) }}">
                                            Edit Profile
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm">
                                <small>Member since: {{ date('d M - Y', $user->created_at->timestamp) }}</small>

                            </div>

                            @if ($user->github)
                                <div class="col-sm">
                                    GitHub: <a href="{{ $user->github }}">GitHub</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($user->bio)
                            Bio: {{ $user->bio }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection