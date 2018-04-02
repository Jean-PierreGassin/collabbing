<li class="list-group-item d-flex justify-content-between align-items-center">
    <a href="{{ route('users.show', $collaborator->user->username) }}">
        {{ $collaborator->user->first_name }} {{ $collaborator->user->last_name }}
    </a>

    @can('deleteApplication', $idea)
        {!! Form::open([
            'route' => ['ideas.applications.destroy', $idea, $collaborator],
            'method' => 'DELETE'
        ]) !!}
        {!! Form::submit('Remove Collaborator 🤕', ['class' => 'btn btn-danger btn-sm']) !!}
        {!! Form::close() !!}
    @endcan
</li>