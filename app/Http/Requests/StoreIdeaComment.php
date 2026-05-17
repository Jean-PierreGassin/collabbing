<?php

namespace App\Http\Requests;

use App\Models\Idea;
use App\Models\IdeaComment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class StoreIdeaComment
 */
class StoreIdeaComment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1500'],
            'parent_id' => ['nullable', 'integer', 'exists:idea_comments,id'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
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
}
