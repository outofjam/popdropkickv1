<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChampionshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:100'],
            'introduced_at' => ['nullable', 'date'],
            'weight_class' => ['nullable', 'string', 'max:100'],
            'active' => ['boolean'],
        ];
    }
}
