<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWrestlerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add your auth logic here
    }

    public function rules(): array
    {
        return [
            'real_name' => ['sometimes', 'string', 'max:255'],
            'debut_date' => ['sometimes', 'nullable', 'date'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],

            'promotions' => ['sometimes', 'array'],
            'promotions.*' => ['uuid', 'exists:promotions,id'],

            'active_promotions' => ['sometimes', 'array'],
            'active_promotions.*' => ['uuid', 'exists:promotions,id'],

            'aliases' => ['sometimes', 'array', 'min:1'],
            'aliases.*.name' => ['required_with:aliases', 'string', 'max:255'],
            'aliases.*.is_primary' => ['boolean'],
            'aliases.*.started_at' => ['nullable', 'date'],
            'aliases.*.ended_at' => ['nullable', 'date'],

            // NEW: update title reigns optionally
            'title_reigns' => ['sometimes', 'array'],
            'title_reigns.*.id' => ['sometimes', 'uuid', 'exists:title_reigns,id'], // For updating existing reigns
            'title_reigns.*.championship_id' => ['required_with:title_reigns.*', 'uuid', 'exists:championships,id'],
            'title_reigns.*.won_on' => ['required_with:title_reigns.*', 'date'],
            'title_reigns.*.won_at' => ['nullable', 'string', 'max:255'],
            'title_reigns.*.lost_on' => ['nullable', 'date', 'after_or_equal:title_reigns.*.won_on'],
            'title_reigns.*.lost_at' => ['nullable', 'string', 'max:255'],
            'title_reigns.*.win_type' => ['required_with:title_reigns.*', 'string', 'max:255'],
            'title_reigns.*.reign_number' => ['required_with:title_reigns.*', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('aliases')) {
                $aliases = $this->input('aliases', []);

                $hasPrimary = collect($aliases)->contains(static fn ($alias) => isset($alias['is_primary']) && $alias['is_primary'] === true);

                if (! $hasPrimary) {
                    $validator->errors()->add('aliases', 'At least one alias must be marked as primary.');
                }
            }
        });
    }
}
