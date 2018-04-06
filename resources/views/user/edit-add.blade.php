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
                    <div class="card-header">{{ isset($user) ? 'Edit' : 'Create' }} Profile</div>

                    <div class="card-body">
                        @if (isset($user))
                            {!! Form::model($user, ['route' => ['users.update', $user->username], 'method' => 'PUT']) !!}
                        @else
                            {!! Form::open(['route' => 'users.store', 'method' => 'POST']) !!}
                        @endif

                        <div class="form-group row">
                            <div class="col">
                                {!! Form::label('first_name', 'First Name') !!}
                                {!! Form::text('first_name', null, [
                                'class' => 'form-control',
                                'placeholder' => 'John',
                                ]) !!}
                            </div>

                            <div class="col">
                                {!! Form::label('last_name', 'Last Name') !!}
                                {!! Form::text('last_name', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Smith',
                                ]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('email', 'E-Mail Address') !!}
                            {!! Form::email('email', null, [
                            'class' => 'form-control',
                            'placeholder' => 'john.smith@apples.com' ,
                            ]) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('github', 'GitHub Profile') !!}
                            {!! Form::url('github', null, [
                            'class' => 'form-control',
                            'placeholder' => 'https://github.com/' . $user->first_name,
                            ]) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('bio', 'Bio') !!}
                            {!! Form::textarea('bio', null, [
                            'class' => 'form-control',
                            'placeholder' => 'Tell us what you\'re good at and what you enjoy...' ,
                            ]) !!}
                        </div>

                        <div class="form-group row">
                            <div class="col">
                                {!! Form::label('password', 'New Password:') !!}
                                {!! Form::password('password', [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <div class="col">
                                {!! Form::label('password_confirmation', 'Confirm Password:') !!}
                                {!! Form::password('password_confirmation', [
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>

                        {!! Form::submit(isset($user) ? 'Edit Profile' : 'Create Profile', ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection