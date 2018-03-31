@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-sm">{{ $idea->title }}</div>

                <div class="col-sm text-sm-right">
                    <a class="btn btn-outline-info btn-sm" href="{{ route('ideas.show', $idea) }}">View</a>

                    @if (Auth::user() && ($idea->user_id === Auth::user()->id))
                        <a class="btn btn-outline-warning btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            Supporters: {{ count($idea->supporters) }}
            Collaborators: {{ count($idea->collaborators) }}
        </div>
    </div>
@endforeach