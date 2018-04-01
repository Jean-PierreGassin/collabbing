@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-3"><a href="{{ route('ideas.show', $idea) }}">{{ $idea->title }}</a></h5>

            <h6 class="card-subtitle text-muted">
                @if (Auth::user() && ($idea->user_id === Auth::user()->id))
                    Status: {{ ucfirst($idea->status) }},&nbsp;
                @endif

                Supporters: {{ count($idea->supporters) }},&nbsp;
                Collaborators: {{ count($idea->collaborators) }}
            </h6>
        </div>

        <div class="card-body">
            <div class="@if (isset($single) && !$single)text-truncate @endif mb-3">{{ $idea->content }}</div>
        </div>

        <h6 class="text-muted text-right mr-2">Created: {{ date('d M - Y', $idea->created_at->timestamp) }}</h6>

        @if (Auth::user() && ($idea->user_id === Auth::user()->id))
            <div class="card-footer">
                <a class="btn btn-secondary btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit Idea</a>

                <a class="btn btn-dark btn-sm" href="{{ route('ideas.applications.index', $idea) }}">
                    Manage Collaborators
                </a>
            </div>
        @endif
    </div>
@endforeach