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

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">Recent Ideas</div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (isset($ideas) && count($ideas) > 0)
                            @include('components.ideas')
                        @else
                            There doesn't seem to be anything here yet...
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection