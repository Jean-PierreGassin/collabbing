@section('title', 'Ideas - Manage')
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
                    <div class="card-header">{{ isset($idea) ? 'Edit' : 'Share' }} your Idea</div>

                    <div class="card-body">
                        <form action="{{ isset($idea) ? route('ideas.update', $idea) : route('ideas.store') }}" method="POST">
                            @csrf
                            @isset($idea)
                                @method('PUT')
                            @endisset

                        <div class="form-group row">
                            <div class="col">
                                <label for="title">Title</label>
                                <input id="title" name="title" type="text" class="form-control"
                                       value="{{ old('title', $idea->title ?? '') }}"
                                       placeholder="A kettle that comes to you!"
                                       aria-describedby="titleHelp">
                                <small id="titleHelp" class="form-text text-muted"></small>
                            </div>

                            <div class="col">
                                <label for="communication">Communication</label>
                                <input id="communication" name="communication" type="text" class="form-control"
                                       value="{{ old('communication', $idea->communication ?? '') }}"
                                       placeholder="e.g Slack, Telegram, KettleChat..."
                                       aria-describedby="communicationHelp">
                                <small id="communicationHelp" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="repository_name">Repository Name</label>
                            <input id="repository_name" name="repository_name" type="text" class="form-control"
                                   value="{{ old('repository_name', $idea->repository_name ?? '') }}"
                                   placeholder="kettle-catastrophe"
                                   aria-describedby="contentHelp">
                            <small id="contentHelp" class="form-text text-muted">
                                This will be the name of your repository once you're ready to create it.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="content">The Pitch (supports markdown)</label>
                            <textarea id="content" name="content" class="form-control"
                                      placeholder="A platform that brings people together to create bangin' ideas."
                                      aria-describedby="contentHelp">{{ old('content', $idea->content ?? '') }}</textarea>
                            <small id="contentHelp" class="form-text text-muted">
                                Make it meaningful and to the point,
                                short and sweet is the best way to get an idea across.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        @if (isset($idea))
                            <div class="float-start">
                                <label for="status">Status:</label>
                                <select id="status" name="status">
                                    <option value="open" @selected(old('status', $idea->status) === 'open')>Open</option>
                                    <option value="closed" @selected(old('status', $idea->status) === 'closed')>Closed</option>
                                </select>
                            </div>
                        @endif

                        <button type="submit" class="{{ isset($idea) ? 'btn btn-dark btn-sm float-end' : 'btn btn-success btn-sm float-end' }}">
                            {{ isset($idea) ? 'Edit Idea 💡' : 'Share Idea 💡' }}
                        </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
