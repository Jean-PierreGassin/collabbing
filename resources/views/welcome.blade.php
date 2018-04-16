@section('title', 'Collaborate Better')
@extends('layouts.errors')

@section('content')
    <div class="m-b-md">
        <h1>Collabbing</h1>
    </div>

    <div class="m-b-md">
        <p class="lead">Collaborate Better.</p>
    </div>

    <a class="btn btn-outline-success" href="{{ route('ideas.index') }}">Browse Ideas</a>
@stop