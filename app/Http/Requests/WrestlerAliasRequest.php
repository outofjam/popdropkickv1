<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WrestlerAliasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'aliases' => 'required|array|min:1',
        'aliases.*.name' => 'required|string|max:255',
        'aliases.*.is_primary' => 'sometimes|boolean',
    ];
}

}
