@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mb-3">
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

                <h3>Recent Ideas</h3>
                @if (isset($ideas) && count($ideas) > 0)
                    @include('components.ideas')
                @else
                    There doesn't seem to be anything here yet...
                @endif
            </div>
        </div>
    </div>
@endsection