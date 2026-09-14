<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkApproveChangeRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canReview();
    }

    public function rules(): array
    {
        return [
            'change_request_ids' => ['required', 'array'],
            'change_request_ids.*' => ['exists:change_requests,id'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
