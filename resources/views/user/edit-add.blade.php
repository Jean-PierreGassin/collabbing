@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ isset($user) ? 'Edit' : 'Create' }} Profile</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (isset($user))
                            {!! Form::model($user, ['route' => 'users.update']) !!}
                            @method('PUT')
                        @else
                            {!! Form::open(['route' => 'users.create']) !!}
                        @endif
                        @csrf

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection