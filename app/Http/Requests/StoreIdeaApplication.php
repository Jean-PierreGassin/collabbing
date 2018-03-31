<?php

namespace App\Http\Requests;

use App\Idea;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaApplication extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        $idea = Idea::find($request->route()->parameter('idea'));

        if ($idea && $idea->user_id === $request->user()->id) {
            return false;
        }

        if ($idea && $idea->applications->contains('user_id', $request->user()->id)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' => 'required|max:500',
        ];
    }
}
