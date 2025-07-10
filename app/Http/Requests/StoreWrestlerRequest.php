<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWrestlerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust authorization logic if needed
    }

    public function rules(): array
    {
        return [
            'real_name' => ['required', 'string', 'max:255'],
            'debut_date' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'max:100'],

            'promotions' => ['required', 'array'],
            'promotions.*' => ['uuid', 'exists:promotions,id'],

            'active_promotions' => ['nullable', 'array'],
            'active_promotions.*' => ['uuid', 'exists:promotions,id'],

            'aliases' => ['required', 'array', 'min:1'],
            'aliases.*.name' => ['required', 'string', 'max:255'],
            'aliases.*.is_primary' => ['boolean'],
            'aliases.*.started_at' => ['nullable', 'date'],
            'aliases.*.ended_at' => ['nullable', 'date'],

            // NEW: Validate optional title reigns array
            'title_reigns' => ['nullable', 'array'],
            'title_reigns.*.championship_id' => ['required', 'uuid', 'exists:championships,id'],
            'title_reigns.*.won_on' => ['required', 'date'],
            'title_reigns.*.won_at' => ['nullable', 'string', 'max:255'],
            'title_reigns.*.lost_on' => ['nullable', 'date', 'after_or_equal:title_reigns.*.won_on'],
            'title_reigns.*.lost_at' => ['nullable', 'string', 'max:255'],
            'title_reigns.*.win_type' => ['required', 'string', 'max:255'],
            'title_reigns.*.reign_number' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $aliases = $this->input('aliases', []);

            $hasPrimary = collect($aliases)->contains(static fn ($alias) => isset($alias['is_primary']) && $alias['is_primary'] === true);

            if (! $hasPrimary) {
                $validator->errors()->add('aliases', 'At least one alias must be marked as primary.');
            }
        });
    }
}
