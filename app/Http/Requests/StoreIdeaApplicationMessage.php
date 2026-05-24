<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaApplicationMessageData;
use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaApplicationMessage extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $body = $this->input('body');

        if (! is_string($body)) {
            return;
        }

        $this->merge([
            'body' => trim($body),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1500'],
        ];
    }

    public function toData(): IdeaApplicationMessageData
    {
        $this->validated();

        return new IdeaApplicationMessageData(
            body: $this->string('body')->trim()->toString()
        );
    }
}
