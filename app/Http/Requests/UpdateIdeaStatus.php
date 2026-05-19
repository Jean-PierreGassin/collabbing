<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaStatusData;
use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIdeaStatus extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Idea::STATUSES)],
        ];
    }

    public function toData(): IdeaStatusData
    {
        $this->validated();

        return new IdeaStatusData(
            status: $this->string('status')->toString()
        );
    }
}
