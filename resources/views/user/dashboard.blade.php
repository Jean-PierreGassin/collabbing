@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">
                                My Ideas
                            </div>

                            <div class="col-sm text-sm-right">
                                <a class="btn btn-outline-success btn-sm" href="{{ route('ideas.create') }}">
                                    Create an Idea
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (isset($ideas))
                            @include('components.ideas')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
