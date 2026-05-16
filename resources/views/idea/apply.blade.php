@section('title', 'Ideas - Apply')
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                    <div class="card-header"><h5>{{ $idea->title }}</h5></div>

                    <div class="card-body">
                        <form action="{{ route('ideas.applications.store', $idea) }}" method="POST">
                            @csrf

                        <div class="form-group">
                            <label for="content">Application</label>
                            <textarea id="content" name="content" class="form-control"
                                      placeholder="I'm really good at water sports even though it has nothing to do with this project."
                                      aria-describedby="contentHelp">{{ old('content') }}</textarea>
                            <small id="contentHelp" class="form-text text-muted">Tell us why you're good for the part,
                                make it interesting and we'll do the rest.
                            </small>
                        </div>

                            <button type="submit" class="btn btn-outline-success">Submit Application 😎</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
