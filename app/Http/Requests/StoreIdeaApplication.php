<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaApplicationData;
use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIdeaApplication extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contribution_type' => [
                'nullable',
                'string',
                Rule::in(Idea::helpAreas()),
            ],
            'first_action' => 'nullable|string|max:280',
            'content' => 'required|max:1500',
        ];
    }

    public function toData(): IdeaApplicationData
    {
        $this->validated();

        return new IdeaApplicationData(
            content: $this->string('content')->toString(),
            contributionType: $this->optionalString('contribution_type'),
            firstAction: $this->optionalString('first_action')
        );
    }

    private function optionalString(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
