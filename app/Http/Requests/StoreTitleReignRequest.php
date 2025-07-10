<?php

namespace App\Http\Requests;

use App\Enums\WinType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTitleReignRequest extends FormRequest
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
    // StoreTitleReignRequest.php (rules)
    public function rules(): array
    {
        return [
            'championship_id' => 'required|exists:championships,id',
            'won_on' => 'required|date',
            'won_at' => 'nullable|string|max:255',
            'lost_on' => 'nullable|date|after_or_equal:won_on',
            'lost_at' => 'nullable|string|max:255',
            'win_type' => ['required', new Enum(WinType::class)],
            //            'reign_number' => 'required|integer|min:1',
        ];
    }
}
