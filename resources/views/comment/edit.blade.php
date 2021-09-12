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
                    <div class="card-header">Edit your Comment</div>

                    <div class="card-body">
                        {!! Form::model($comment, ['route' => ['ideas.comments.update', $idea, $comment], 'method' => 'PUT']) !!}

                        <div class="form-group">
                            {!! Form::label('content', 'Comment') !!}
                            {!! Form::textarea('content', null, [
                            'class' => 'form-control',
                            'aria-describedby' => 'contentHelp',
                            ]) !!}
                            <small id="contentHelp" class="form-text text-muted">Nobody likes a bossy boots, think
                                before you type.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        {!! Form::submit('Edit Comment', ['class' => 'btn btn-dark btn-sm float-right']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection