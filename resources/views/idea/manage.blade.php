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

                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active"
                           id="pills-applications-tab" data-toggle="pill" href="#pills-ideas"
                           role="tab"
                           aria-controls="pills-ideas" aria-selected="true">Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           id="pills-collaborators-tab" data-toggle="pill" href="#pills-collaborating"
                           role="tab"
                           aria-controls="pills-collaborating" aria-selected="false">Collaborators</a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active"
                         id="pills-ideas" role="tabpanel"
                         aria-labelledby="pills-applications-tab">
                        <div class="row">
                            <div class="col-sm">
                                @if (isset($applications) && count($applications) > 0)
                                    @foreach ($applications as $application)
                                        @include('components.applicants')
                                    @endforeach
                                @else
                                    <i>~ tumbleweed</i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade"
                         id="pills-collaborating" role="tabpanel"
                         aria-labelledby="nav-collaborators-tab">
                        <div class="row">
                            <div class="col-sm">
                                @if (isset($collaborators) && count($collaborators) > 0)
                                    <ul class="list-group mb-3">
                                        @foreach ($collaborators as $collaborator)
                                            @include('components.collaborators')
                                        @endforeach
                                    </ul>
                                @else
                                    <p>Looks a little lonely in here 😰</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection