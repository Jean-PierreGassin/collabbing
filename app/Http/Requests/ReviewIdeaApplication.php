<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaApplicationDecisionData;
use Illuminate\Foundation\Http\FormRequest;

class ReviewIdeaApplication extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->routeIs('ideas.applications.approve')) {
            return [
                'approval_note' => ['nullable', 'string', 'max:1200'],
                'decline_reason' => ['prohibited'],
            ];
        }

        if ($this->routeIs('ideas.applications.destroy')) {
            return [
                'approval_note' => ['prohibited'],
                'decline_reason' => ['nullable', 'string', 'max:1200'],
            ];
        }

        return [
            'approval_note' => ['prohibited'],
            'decline_reason' => ['prohibited'],
        ];
    }

    public function toData(): IdeaApplicationDecisionData
    {
        $this->validated();

        return new IdeaApplicationDecisionData(
            approvalNote: $this->optionalString('approval_note'),
            declineReason: $this->optionalString('decline_reason')
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
