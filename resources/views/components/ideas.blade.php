@foreach ($ideas as $idea)
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm">{{ $idea->title }}</div>

                <div class="col-sm text-sm-right">
                    <a class="btn btn-outline-info btn-sm" href="{{ route('ideas.show', $idea) }}">View</a>
                    <a class="btn btn-outline-warning btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            Supporters: {{ count($idea->supporters) }}
            Collaborators: {{ count($idea->collaborators) }}
        </div>
    </div>
@endforeach