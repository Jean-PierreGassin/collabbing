@foreach ($ideas as $idea)
    <div class="card mb-3">
        <div class="card-header border-0">
            <div class="row">
                <div class="col-8">
                    <h5 class="mb-3">
                        @if (!isset($single))
                            <a href="{{ route('ideas.show', $idea) }}">{{ ucfirst($idea->title) }}</a>
                        @else
                            {{ ucfirst($idea->title) }} - <small>by <a
                                        href="{{ route('users.show', $idea->user->username) }}">{{ $idea->user->username }}</a></small>
                        @endif
                    </h5>
                </div>

                @can('update', $idea)
                    <div class="col-4 text-right">
                        Status:&nbsp;
                        <span class="{{ $idea->status === 'open' ? 'text-success' : 'text-danger' }}">
                            {{ ucfirst($idea->status) }}
                        </span>
                    </div>
                @endcan
            </div>
        </div>

        @if (isset($single))
            <div class="card-body">
                <div>
                    {!! \GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($idea->content) !!}
                </div>
            </div>

            <h6 class="text-muted text-right mr-2 mt-3"><i>Created {{ $idea->created_at->diffForHumans() }}</i></h6>
        @endif

        <div class="card-footer border-0">
            @can('update', $idea)
                <a class="btn btn-dark btn-sm" href="{{ route('ideas.edit', $idea) }}">
                    Edit Idea
                </a>

                <a class="btn btn-info btn-sm" href="{{ route('ideas.dashboard', $idea) }}">
                    Idea Dashboard
                </a>
            @endcan

            @if (!isset($single))
                <div class="row">
                    <div class="col-6">
                        <div class="text-left">
                            Supporters: {{ number_format(count($idea->supporters)) }},
                            Collaborators: {{ number_format(count($idea->approvedApplications)) }}
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-right text-muted">
                            <i>Created {{ $idea->created_at->diffForHumans() }}</i>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (isset($single) && (Gate::check('update', $idea) || Gate::check('manage', $idea->comments) || isset($collaborator)))
        @include('components.comments')
        @include('comment.add')
    @endif
@endforeach


