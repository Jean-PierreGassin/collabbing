@section('title', 'Dashboard')
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

                <div class="row rounded pt-2 pb-2 mb-5">
                    <div class="col-8">
                        <ul class="nav nav-pills" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link
                            {{ str_contains(Request::getQueryString(), 'ideas') || !Request::getQueryString() ? 'active' : '' }}"
                                   id="pills-ideas-tab" data-toggle="pill" href="#pills-ideas"
                                   role="tab"
                                   aria-controls="pills-ideas" aria-selected="true">My Ideas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link
                           {{ str_contains(Request::getQueryString(), 'collaborations') ? 'active' : '' }}"
                                   id="pills-collaborating-tab" data-toggle="pill" href="#pills-collaborating"
                                   role="tab"
                                   aria-controls="pills-collaborating" aria-selected="false">Ideas I'm collaborating on</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-4 text-right">
                        <a class="btn btn-outline-success btn-sm"
                           href="{{ route('ideas.create') }}">
                            Create an Idea
                        </a>
                    </div>
                </div>


                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade
                         {{ str_contains(Request::getQueryString(), 'ideas') || !Request::getQueryString() ? 'show active' : '' }}"
                         id="pills-ideas" role="tabpanel"
                         aria-labelledby="pills-ideas-tab">
                        <div class="row">
                            <div class="col-sm">
                                @if (isset($ideas) && count($ideas) > 0)
                                    @include('components.ideas', ['ideas' => $ideas])
                                @else
                                    You're fresh out, why not <a href="{{ route('ideas.create') }}">create one?</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade
                         {{ str_contains(Request::getQueryString(), 'collaborations') ? 'show active' : '' }}"
                         id="pills-collaborating" role="tabpanel"
                         aria-labelledby="nav-collaborating-tab">
                        <div class="row">
                            <div class="col-sm">
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
        </div>
    </div>
@endsection
