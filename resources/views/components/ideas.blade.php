@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header border-0">
            <h5 class="mb-3">
                @if (!isset($single))
                    <a href="{{ route('ideas.show', $idea) }}">{{ $idea->title }}</a>
                @else
                    {{ $idea->title }} - <small>by <a href="{{ route('users.show', $idea->user->username) }}">{{ $idea->user->username }}</a></small>
                @endif
            </h5>

            <h6 class="card-subtitle text-muted">
                @can('update', $idea)
                    <div class="float-right">
                        Status:&nbsp;
                        <span class="float-right {{ $idea->status === 'open' ? 'text-success' : 'text-danger' }}">
                            {{ ucfirst($idea->status) }}
                        </span>
                    </div>
                @endcan

                Supporters: {{ number_format(count($idea->supporters)) }},
                Collaborators: {{ number_format(count($idea->approvedApplications)) }}

                @if (!isset($single))
                    <div class="float-right">
                        <i>Created {{ $idea->created_at->diffForHumans() }}</i>
                    </div>
                @endif
            </h6>
        </div>

        @if (isset($single))
            <div class="card-body">
                <div>
                    {!! \GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($idea->content) !!}
                </div>
            </div>

            <h6 class="text-muted text-right mr-2 mt-3"><i>Created {{ $idea->created_at->diffForHumans() }}</i></h6>
        @endif

        @can('update', $idea)
            <div class="card-footer border-0">
                <a class="btn btn-dark btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit Idea</a>

                <a class="btn btn-info btn-sm float-right" href="{{ route('ideas.dashboard', $idea) }}">
                    Idea Dashboard
                </a>
            </div>
        @endcan
    </div>

    @if (isset($single, $collaborator) && (Gate::check('update', $idea) || Gate::check('manage', $idea->comments) || $collaborator))
        @include('components.comments')
        @include('comment.add')
    @endif
@endforeach

@if (!isset($single))
    @include('components.pagination', ['data' => $ideas])
@endif
