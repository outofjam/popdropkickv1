<?php

namespace App\Http\Requests;

use App\Enums\WinType;
use App\Models\TitleReign;
use App\Models\WrestlerName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTitleReignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'championship_id'         => ['sometimes', 'exists:championships,id'],
            'wrestler_id'             => ['sometimes', 'exists:wrestlers,id'], // if you allow changing the wrestler
            'won_on'                  => ['sometimes', 'date'],
            'won_at'                  => ['sometimes', 'nullable', 'string', 'max:255'],
            'lost_on'                 => ['sometimes', 'nullable', 'date', 'after_or_equal:won_on'],
            'lost_at'                 => ['sometimes', 'nullable', 'string', 'max:255'],
            'win_type'                => ['sometimes', new Enum(WinType::class)],
            'reign_number'            => ['sometimes', 'integer', 'min:1'],
            'wrestler_name_id_at_win' => ['sometimes', 'nullable', 'exists:wrestler_names,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // only check if alias field is present in this update
            if (! $this->has('wrestler_name_id_at_win')) {
                return;
            }

            $aliasId = $this->input('wrestler_name_id_at_win');
            if ($aliasId === null || $aliasId === '') {
                return; // explicitly clearing is allowed
            }

            $alias = WrestlerName::query()
                ->select(['id', 'wrestler_id'])
                ->find($aliasId);

            if (! $alias) {
                $v->errors()->add('wrestler_name_id_at_win', 'Selected alias was not found.');
                return;
            }

            // Resolve the route param (can be a model or a raw id/slug depending on route)
            $routeReign = $this->route('reign');
            $reign = $routeReign instanceof TitleReign
                ? $routeReign
                : TitleReign::query()->find($routeReign);

            if (! $reign) {
                $v->errors()->add('reign', 'Unable to resolve the title reign from the route.');
                return;
            }

            // If request tries to change wrestler_id in the same update, use the incoming value for comparison
            $targetWrestlerId = $this->input('wrestler_id', $reign->wrestler_id);

            if ((string) $alias->wrestler_id !== (string) $targetWrestlerId) {
                $v->errors()->add(
                    'wrestler_name_id_at_win',
                    'The alias must belong to the reign’s wrestler.'
                );
            }
        });
    }
}
