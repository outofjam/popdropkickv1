<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canReview();
    }

    public function rules(): array
    {
        return [];
    }
}
