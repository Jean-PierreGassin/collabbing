<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col">Collaborators</div>

            @can('storeApplication', $idea)
                <div class="col text-sm-right">
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
        @if (count($idea->approvedApplications) === 0)
            It's quite... too quite.
        @else
            @foreach ($idea->approvedApplications as $collaborator)
                <a href="{{ route('users.show', $collaborator->user->username) }}">
                    <img class="border border-secondary"
                         width="40" height="40"
                         src="{{ $collaborator->user->profilePicture() }}"
                         alt="{{ $collaborator->user->first_name }} {{ $collaborator->user->last_name }}"
                    >
                </a>
            @endforeach
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col">Supporters</div>

            @can('storeSupporter', $idea)
                <div class="col text-sm-right">
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
        @if (count($idea->supporters) > 0)
            There {{ count($idea->supporters) > 1 ? 'are' : 'is' }}
            {{ number_format(count($idea->supporters)) }}
            {{ count($idea->supporters) > 1 ? 'people' : 'person' }} supporting this idea.
        @else
            Ain't nobody supportin' this here idea yet.
        @endif
    </div>
</div>
