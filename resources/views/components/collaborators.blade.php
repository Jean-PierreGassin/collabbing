<li class="list-group-item d-flex justify-content-between align-items-center">
    <a href="{{ route('users.show', $collaborator->user->username) }}">
        {{ $collaborator->user->first_name }} {{ $collaborator->user->last_name }}
    </a>

    @can('deleteApplication', $idea)
        <form action="{{ route('ideas.applications.destroy', [$idea, $collaborator]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Remove Collaborator 🤕</button>
        </form>
    @endcan
</li>
