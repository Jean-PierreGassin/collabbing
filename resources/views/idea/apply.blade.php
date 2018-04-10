@section('title', 'Ideas - Apply')
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

                @if (session('errors'))
                    <div class="alert alert-danger">
                        @foreach (session('errors')->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </div>
                @endif

                <div class="card">
                    <div class="card-header"><h5>{{ $idea->title }}</h5></div>

                    <div class="card-body">
                        {!! Form::open(['route' => ['ideas.applications.store', $idea], 'method' => 'POST']) !!}

                        <div class="form-group">
                            {!! Form::label('content', 'Application') !!}
                            {!! Form::textarea('content', null, [
                            'class' => 'form-control',
                            'placeholder' => 'I\'m really good at water sports even though it has nothing to do with this project.' ,
                            'aria-describedby' => 'contentHelp',
                            ]) !!}
                            <small id="contentHelp" class="form-text text-muted">Tell us why you're good for the part,
                                make it interesting and we'll do the rest.
                            </small>
                        </div>

                        {!! Form::submit('Submit Application 😎', ['class' => 'btn btn-outline-success']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection