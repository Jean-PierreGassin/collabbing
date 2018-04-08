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

                @include('components.ideas', ['ideas' => [$idea], 'single' => true])
            </div>

            <div class="col-md-4">
                @include('components.idea-sidebar')
            </div>
        </div>
    </div>
@endsection