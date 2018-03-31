@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ isset($idea) ? 'Edit' : 'Share' }} an Idea 💡</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (isset($idea))
                            {!! Form::model($idea, ['route' => 'ideas.update']) !!}
                            @method('PUT')
                        @else
                            {!! Form::open(['route' => 'ideas.create']) !!}
                        @endif
                        @csrf

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection