@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-3"><a href="{{ route('ideas.show', $idea) }}">{{ $idea->title }}</a></h5>

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
            </h6>
        </div>

        <div class="card-body">
            <div class="mb-3">
                @if (!isset($single))
                    {!! str_limit(nl2br(e($idea->content)), 300) !!}
                @else
                    {!! nl2br(e($idea->content)) !!}
                @endif
            </div>
        </div>

        <h6 class="text-muted text-right mr-2">Created {{ $idea->created_at->diffForHumans() }}</h6>

        @can('update', $idea)
            <div class="card-footer">
                <a class="btn btn-dark btn-sm" href="{{ route('ideas.edit', $idea) }}">Edit Idea</a>

                <a class="btn btn-info btn-sm float-right" href="{{ route('ideas.dashboard', $idea) }}">
                    Idea Dashboard
                </a>
            </div>
        @endcan
    </div>

    @if (isset($single) && $idea->comments()->count() > 0 && (Gate::check('update', $idea) || Gate::check('manage', $idea->comments)))
        @include('components.comments')
        @include('comment.add')
    @endif
@endforeach

@if (!isset($single))
    @include('components.pagination', ['data' => $ideas])
@endif
