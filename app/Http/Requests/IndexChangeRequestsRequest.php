<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexChangeRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canReview();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string'],
            'model_type' => ['sometimes', 'string'],
            'action' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
