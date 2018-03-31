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
                    <div class="card-header">{{ isset($idea) ? 'Edit' : 'Share' }} an Idea</div>

                    <div class="card-body">
                        @if (isset($idea))
                            {!! Form::model($idea, ['route' => ['ideas.update', $idea], 'method' => 'PUT']) !!}
                        @else
                            {!! Form::open(['route' => 'ideas.store', 'method' => 'POST']) !!}
                        @endif

                        <div class="row">
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
                                {!! Form::label('communication', 'Preferred Communication') !!}
                                {!! Form::text('communication', null, [
                                'class' => 'form-control',
                                'placeholder' => 'e.g Slack, Telegram, KettleChat...',
                                'aria-describedby' => 'communicationHelp',
                                ]) !!}
                                <small id="communicationHelp" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('content', 'Description') !!}
                            {!! Form::textarea('content', null, [
                            'class' => 'form-control',
                            'placeholder' => 'A platform that brings people together to create bangin\' ideas.' ,
                            'aria-describedby' => 'contentHelp',
                            ]) !!}
                            <small id="contentHelp" class="form-text text-muted">Make it meaningful and to the point,
                                short and sweet is the best way to get an idea across.
                            </small>
                        </div>

                        @if (isset($idea))
                            <div class="form-group">
                                {!! Form::label('status', 'Status:') !!}
                                {!! Form::select('status', [
                                    'open' => 'Open',
                                    'closed' => 'Closed',
                                ]) !!}
                                <small id="statusHelp" class="form-text text-muted"></small>
                            </div>
                        @endif

                        {!! Form::submit(isset($idea) ? 'Edit Idea 💡' : 'Share Idea 💡', ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection