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
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">Collaborators</div>

                            @can('storeApplication', $idea)
                                <div class="col-sm text-sm-right">
                                    @if ($collaborator)
                                        <a class="btn btn-success btn-sm"
                                           href="#">
                                            You're a Collaborator 🤟
                                        </a>
                                    @elseif ($applicant)
                                        <a class="btn btn-success btn-sm"
                                           href="#">
                                            You're an Applicant ✅
                                        </a>
                                    @else
                                        <a class="btn btn-outline-success btn-sm"
                                           href="{{ route('ideas.applications.create', $idea) }}">
                                            Apply to Collaborate 📝
                                        </a>
                                    @endif
                                </div>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        {{ count($idea->approvedApplications) }}
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm">Supporters</div>

                            @can('storeSupporter', $idea)
                                <div class="col-sm text-sm-right">
                                    @if ($supporter)
                                        {!! Form::open([
                                            'route' => ['ideas.supporters.destroy', $idea, $supporter],
                                            'method' => 'DELETE'
                                        ]) !!}
                                        {!! Form::submit('Un-Support this Idea 👎', ['class' => 'btn btn-info btn-sm']) !!}
                                    @else
                                        {!! Form::open(['route' => ['ideas.supporters.store', $idea], 'method' => 'POST']) !!}
                                        {!! Form::submit('Support this Idea 👍', ['class' => 'btn btn-outline-info btn-sm']) !!}
                                    @endif

                                    {!! Form::close() !!}
                                </div>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        There {{ count($idea) > 1 ? 'are' : 'is' }}
                        {{ number_format(count($idea->supporters)) }}
                        {{ count($idea) > 1 ? 'people' : 'person' }} supporting this idea.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection