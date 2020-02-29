<h4 class="mt-4">Collaborator Comments</h4>

@foreach ($idea->comments()->paginate(10) as $comment)
    <div class="card mb-2">
        <div class="card-header">
            <h6 class="d-inline">
                <a href="{{ route('users.show', $comment->user->username) }}">
                    {{ '@'. $comment->user->username }}
                </a>

                <span class="text-muted">
                    Posted {{ $comment->created_at->diffForHumans() }}
                </span>
            </h6>
        </div>

        <div class="card-body">
            <div class="mb-3">
                {!! nl2br(e($comment->content)) !!}
            </div>
        </div>

        @if ($comment->created_at->timestamp < $comment->updated_at->timestamp)
            <h6 class="text-muted text-right mr-2">Last edited {{ $comment->updated_at->diffForHumans() }}</h6>
        @endif

        @can('update', $comment)
            <div class="card-footer">
                <a class="btn btn-dark btn-sm" href="{{ route('ideas.comments.edit', [$idea, $comment]) }}">Edit
                    Comment</a>
            </div>
        @endcan
    </div>
@endforeach

@include('components.pagination', ['data' => $idea->comments()->paginate(10)])
