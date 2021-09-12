@section('title', '@' . $user->username)
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
                    <div class="card-header">
                        <h5>
                            <a href="{{ route('users.show', $user->username) }}">{{ '@' . $user->username }}</a>

                            @can('update', $user)
                                <a class="btn btn-dark btn-sm float-right"
                                   href="{{ route('users.edit', $user->username) }}">
                                    Edit Profile
                                </a>
                            @endcan
                        </h5>

                        <h6 class="card-subtitle text-muted">
                            Member since: {{ date('d M - Y', $user->created_at->timestamp) }}

                            @if ($user->github_username)
                                - <a href="https://github.com/{{ $user->github_username }}">GitHub Profile</a>
                            @endif
                        </h6>
                    </div>

                    <div class="card-body">
                        @if ($user->bio)
                            <blockquote class="blockquote">
                                <p class="mb-0">
                                    {!! \GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($user->bio) !!}
                                </p>
                                <footer class="blockquote-footer">Someone called
                                    <cite title="Source Title">
                                        {{ ucwords($user->first_name) . ' ' . ucwords($user->last_name) }}
                                    </cite>
                                </footer>
                            </blockquote>
                        @else
                            <blockquote class="blockquote">
                                <p class="mb-0">I've got nothing good to say</p>
                                <footer class="blockquote-footer">Someone called
                                    <cite title="Source Title">
                                        {{ ucwords($user->first_name) . ' ' . ucwords($user->last_name) }}
                                    </cite>
                                </footer>
                            </blockquote>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection