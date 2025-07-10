<?php

namespace App\Http\Requests;

use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWrestlerPromotionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add auth logic if needed
    }

    public function rules(): array
    {
        return [
            'add_promotions' => ['sometimes', 'array'],
            'add_promotions.active' => ['sometimes', 'array'],
            'add_promotions.active.*' => ['string'],
            'add_promotions.inactive' => ['sometimes', 'array'],
            'add_promotions.inactive.*' => ['string'],

            'remove_promotions' => ['sometimes', 'array'],
            'remove_promotions.*' => ['string'],

            'deactivate_promotions' => ['sometimes', 'array'],
            'deactivate_promotions.*' => ['string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $allIdentifiers = array_merge(
                $this->input('add_promotions.active', []),
                $this->input('add_promotions.inactive', []),
                $this->input('remove_promotions', []),
                $this->input('deactivate_promotions', [])
            );

            if (! empty($allIdentifiers)) {
                // Validate identifiers exist in promotions table by id or slug or name or abbreviation
                $count = Promotion::whereIn('id', $allIdentifiers)
                    ->orWhereIn('slug', $allIdentifiers)
                    ->orWhereIn('name', $allIdentifiers)
                    ->orWhereIn('abbreviation', $allIdentifiers)
                    ->count();

                if ($count < count(array_unique($allIdentifiers))) {
                    $validator->errors()->add('promotions', 'One or more promotion identifiers are invalid.');
                }
            }
        });
    }
}
