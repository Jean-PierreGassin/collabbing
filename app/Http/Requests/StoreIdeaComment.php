<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaCommentData;
use App\Models\Idea;
use App\Models\IdeaComment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreIdeaComment extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1500'],
            'parent_id' => ['nullable', 'integer', 'exists:idea_comments,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $idea = $this->route('idea');
                $parentId = $this->integer('parent_id');

                if (! $idea instanceof Idea || $parentId === 0) {
                    return;
                }

                $isSameIdeaComment = IdeaComment::query()
                    ->whereKey($parentId)
                    ->where('idea_id', $idea->id)
                    ->exists();

                if (! $isSameIdeaComment) {
                    $validator->errors()->add('parent_id', 'Reply to a comment on this idea.');
                }
            },
        ];
    }

    public function toData(): IdeaCommentData
    {
        $validated = $this->validated();
        $parentId = null;

        if (array_key_exists('parent_id', $validated) && $validated['parent_id'] !== null) {
            $parentId = (int) $validated['parent_id'];
        }

        return new IdeaCommentData(
            content: $this->string('content')->toString(),
            parentId: $parentId
        );
    }
}
