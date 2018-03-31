@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-info">
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
                        <div class="row">
                            <div class="col-sm">
                                {{ ucfirst($idea->title) }}
                            </div>

                            <div class="col-sm text-sm-right">
                                @auth
                                    @if (Auth::user()->id === $idea->user_id)
                                        <a class="btn btn-warning btn-sm" href="{{ route('ideas.edit', $idea) }}">
                                            Edit Idea
                                        </a>

                                        <a class="btn btn-dark btn-sm"
                                           href="{{ route('ideas.applications.index', $idea) }}">
                                            Manage Collaborators
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

                    @auth
                        @if (Auth::user()->id !== $idea->user_id)
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm">
                                        @php $userSupports = $idea->supporters->contains('user_id', Auth::user()->id); @endphp

                                        @if ($userSupports)
                                            @php $supporter = $idea->supporters->where('user_id', Auth::user()->id)->first(); @endphp
                                            {!! Form::open([
                                                'route' => ['ideas.supporters.destroy', $idea, $supporter],
                                                'method' => 'DELETE'
                                            ]) !!}
                                            {!! Form::submit('Un-Support this Idea 👎', ['class' => 'btn btn-dark btn-sm']) !!}
                                        @else
                                            {!! Form::open(['route' => ['ideas.supporters.store', $idea], 'method' => 'POST']) !!}
                                            {!! Form::submit('Support this Idea 👍', ['class' => 'btn btn-outline-dark btn-sm']) !!}
                                        @endif

                                        {!! Form::close() !!}
                                    </div>

                                    <div class="col-sm text-sm-right">
                                        <a class="btn btn-outline-success btn-sm"
                                           href="{{ route('ideas.applications.create', $idea) }}">
                                            Apply to Collaborate 📝
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">Collaborators</div>

                    <div class="card-body">
                        {{ count($idea->collaborators) }}
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Supporters</div>

                    <div class="card-body">
                        {{ count($idea->supporters) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection