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

                <div id="ideas">
                    <h3>My Ideas
                        <a class="btn btn-outline-success btn-sm float-right" href="{{ route('ideas.create') }}">
                            Create an Idea
                        </a>
                    </h3>

                    <div class="row">
                        <div class="col-sm">
                            @if (isset($ideas) && count($ideas) > 0)
                                @include('components.ideas', ['ideas' => $ideas, 'container' => 'ideas'])
                            @else
                                You're fresh out, why not <a href="{{ route('ideas.create') }}">create one?</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="collaborations">
                    <div class="row">
                        <div class="col-sm">
                            <h3>Ideas I'm collaborating on</h3>
                        </div>
                    </div>

                    <div id="collaborations" class="row">
                        <div class="col-sm">
                            @if (isset($collaborations) && count($collaborations) > 0)
                                @include('components.ideas', ['ideas' => $collaborations, 'container' => 'collaborations'])
                            @else
                                Don't be shy, <a href="{{ route('ideas.index') }}">start applying!</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
