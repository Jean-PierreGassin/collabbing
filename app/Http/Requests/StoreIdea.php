<?php

namespace App\Http\Requests;

use App\Idea;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class StoreIdea extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        $idea = Idea::find($request->idea->id);

        if ($idea && $idea->user_id) {
            if ($request->user()->id === $idea->user_id) {
                return true;
            }

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
            'title' => 'required|max:100',
            'communication' => 'required|max:30',
            'content' => 'required|max:500',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'content.required' => 'The description field is required',
            'content.max' => 'The description may not be greater than 500 characters.',
        ];
    }
}
