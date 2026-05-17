<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreIdea
 */
class StoreIdea extends FormRequest
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
            'title' => 'required|max:100',
            'repository_name' => 'required|alpha_dash|max:50',
            'communication' => 'required|max:50',
            'content' => 'required|max:1500',
            'status' => 'in:open,closed',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'The description field is required',
            'content.max' => 'The description may not be greater than 1500 characters.',
        ];
    }
}
