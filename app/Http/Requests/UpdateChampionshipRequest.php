<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChampionshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add any auth logic if needed or just return true for now
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'active' => 'sometimes|boolean',
            // other updatable fields here
        ];
    }
}
