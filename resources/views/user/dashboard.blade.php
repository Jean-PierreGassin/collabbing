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
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">My Ideas</div>

                            <div class="col-sm text-sm-right">
                                <a class="btn btn-outline-success btn-sm" href="{{ route('ideas.create') }}">
                                    Create an Idea
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (isset($ideas) && count($ideas) > 0)
                            @include('components.ideas', ['ideas' => $ideas])
                        @else
                            You're fresh out, why not <a href="{{ route('ideas.create') }}">create one?</a>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">Ideas I'm Collaborating On</div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (isset($collaborations) && count($collaborations) > 0)
                            @include('components.ideas', ['ideas' => $collaborations])
                        @else
                            Don't be shy, <a href="{{ route('ideas.index') }}">start applying!</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
