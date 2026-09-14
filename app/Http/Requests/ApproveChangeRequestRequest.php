<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canReview();
    }

    public function rules(): array
    {
        return [
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
