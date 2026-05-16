<div class="card mb-4">
    <div class="card-header">Share your Comment</div>

    <div class="card-body">
        <form action="{{ route('ideas.comments.store', $idea) }}" method="POST">
            @csrf

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" class="form-control"
                      placeholder="I liked the thing you said about the other thing, however I prefer to do it this way instead"
                      aria-describedby="contentHelp">{{ old('content') }}</textarea>
            <small id="contentHelp" class="form-text text-muted">Nobody likes a bossy boots, think
                before you type.
            </small>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-success btn-sm float-end">Share Comment</button>
        </form>
    </div>
</div>
