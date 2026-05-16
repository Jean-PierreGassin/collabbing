<div id="accordion">
    <div class="card mb-2">
        <div class="card-header" id="{{ $application->user_id }}-header">
            <h6 class="mb-0">
                <a href="{{ route('users.show', $application->user->username) }}">{{ $application->user->name }}'s Application</a>
            </h6>
        </div>

        <div id="{{ $application->id }}">
            <div class="card-body">
                {!! nl2br(e($application->content)) !!}
            </div>

            <h6 class="text-muted text-end me-2">Submitted {{ $idea->created_at->diffForHumans() }}</h6>

            <div class="card-footer">
                <div class="row">
                    <div class="col-sm">
                        @can('deleteApplication', $idea)
                            <form action="{{ route('ideas.applications.destroy', [$idea, $application]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Decline this Application 👎</button>
                            </form>
                        @endcan
                    </div>

                    <div class="col-sm text-sm-right">
                        @can('updateApplication', $idea)
                            <form action="{{ route('ideas.applications.approve', [$idea, $application]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm float-end">Approve this Application ✅</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
