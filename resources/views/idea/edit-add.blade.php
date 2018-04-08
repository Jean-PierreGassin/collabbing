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
                    <div class="card-header">{{ isset($idea) ? 'Edit' : 'Share' }} your Idea</div>

                    <div class="card-body">
                        @if (isset($idea))
                            {!! Form::model($idea, ['route' => ['ideas.update', $idea], 'method' => 'PUT']) !!}
                        @else
                            {!! Form::open(['route' => 'ideas.store', 'method' => 'POST']) !!}
                        @endif

                        <div class="form-group row">
                            <div class="col">
                                {!! Form::label('title', 'Title') !!}
                                {!! Form::text('title', null, [
                                'class' => 'form-control',
                                'placeholder' => 'A kettle that comes to you!',
                                'aria-describedby' => 'titleHelp',
                                ]) !!}
                                <small id="titleHelp" class="form-text text-muted"></small>
                            </div>

                            <div class="col">
                                {!! Form::label('communication', 'Communication') !!}
                                {!! Form::text('communication', null, [
                                'class' => 'form-control',
                                'placeholder' => 'e.g Slack, Telegram, KettleChat...',
                                'aria-describedby' => 'communicationHelp',
                                ]) !!}
                                <small id="communicationHelp" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('repository_name', 'Repository Name') !!}
                            {!! Form::text('repository_name', null, [
                            'class' => 'form-control',
                            'placeholder' => 'kettle-catastrophe' ,
                            'aria-describedby' => 'contentHelp',
                            ]) !!}
                            <small id="contentHelp" class="form-text text-muted">
                                This will be the name of your repository once you're ready to create it.
                            </small>
                        </div>

                        <div class="form-group">
                            {!! Form::label('content', 'The Pitch') !!}
                            {!! Form::textarea('content', null, [
                            'class' => 'form-control',
                            'placeholder' => 'A platform that brings people together to create bangin\' ideas.' ,
                            'aria-describedby' => 'contentHelp',
                            ]) !!}
                            <small id="contentHelp" class="form-text text-muted">
                                Make it meaningful and to the point,
                                short and sweet is the best way to get an idea across (supports markdown).
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        @if (isset($idea))
                            <div class="float-left">
                                {!! Form::label('status', 'Status:') !!}
                                {!! Form::select('status', [
                                    'open' => 'Open',
                                    'closed' => 'Closed',
                                ]) !!}
                            </div>
                        @endif

                        {!! Form::submit(isset($idea) ? 'Edit Idea 💡' : 'Share Idea 💡', [
                            'class' => isset($idea) ? 'btn btn-dark btn-sm float-right' : 'btn btn-success btn-sm float-right',
                        ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection