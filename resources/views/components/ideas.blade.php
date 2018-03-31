@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-sm">
                    <a href="{{ route('ideas.show', $idea) }}">{{ $idea->title }}</a>
                </div>

                <div class="col-sm text-sm-right">
                    @if (Auth::user() && ($idea->user_id === Auth::user()->id))
                        <a class="btn btn-warning btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit Idea</a>
                    @endif

                    @if (Auth::user() && ($idea->user_id === Auth::user()->id))
                        <a class="btn btn-dark btn-sm" href="{{ route('ideas.applications.index', $idea) }}">Manage Collaborators</a>
                    @endif
                </div>
            </div>

            @if (Auth::user() && ($idea->user_id === Auth::user()->id))
                <hr>

                <small>Status: {{ ucfirst($idea->status) }}</small>
            @endif
        </div>

        <div class="card-body">
            Supporters: {{ count($idea->supporters) }}
            Collaborators: {{ count($idea->collaborators) }}
        </div>
    </div>
@endforeach