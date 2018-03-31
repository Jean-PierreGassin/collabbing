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
                                {{ ucfirst($idea->title) }}
                            </div>

                            <div class="col-sm text-sm-right">
                                @auth
                                    @if (Auth::user()->id === $idea->user_id)
                                        <a class="btn btn-dark btn-sm" href="{{ route('ideas.edit', $idea) }}">
                                            Edit Idea
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <hr>

                        <small>Status: {{ ucfirst($idea->status) }}</small>
                    </div>

                    <div class="card-body">
                        {!!  nl2br($idea->content) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection