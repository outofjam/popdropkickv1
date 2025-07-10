<?php

namespace App\Http\Requests;

use App\Enums\WinType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTitleReignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'championship_id' => 'sometimes|exists:championships,id',
            'won_on' => 'sometimes|date',
            'won_at' => 'sometimes|nullable|string|max:255',
            'lost_on' => 'sometimes|nullable|date|after_or_equal:won_on',
            'lost_at' => 'sometimes|nullable|string|max:255',
            'win_type' => ['sometimes', 'required', new Enum(WinType::class)],
            'reign_number' => 'sometimes|required|integer|min:1',
        ];
    }
}
