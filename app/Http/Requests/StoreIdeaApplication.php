<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaApplicationData;
use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaApplication extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|max:1500',
        ];
    }

    public function toData(): IdeaApplicationData
    {
        $this->validated();

        return new IdeaApplicationData(
            content: $this->string('content')->toString()
        );
    }
}
