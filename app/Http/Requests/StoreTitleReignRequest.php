<?php

namespace App\Http\Requests;

use App\Enums\WinType;
use App\Models\Championship;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTitleReignRequest extends FormRequest
{
    protected ?Championship $resolvedChampionship = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize championship_id to a real ID (accepts id or slug).
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('championship_id')) {
            return;
        }

        $input = (string) $this->input('championship_id');

        $championship = Championship::query()
            ->where(fn ($q) => $q->whereKey($input)->orWhere('slug', $input))
            ->firstOrFail();

        $this->resolvedChampionship = $championship;

        $this->merge([
            'championship_id' => $championship->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'championship_id'         => ['required', 'exists:championships,id'],
            'won_on'                  => ['required', 'date'],
            'won_at'                  => ['nullable', 'string', 'max:255'],
            'lost_on'                 => ['nullable', 'date', 'after_or_equal:won_on'],
            'lost_at'                 => ['nullable', 'string', 'max:255'],
            'win_type'                => ['required', new Enum(WinType::class)],
            // Optional: defaults to primary alias in the Service if omitted
            'wrestler_name_id_at_win' => ['nullable', 'exists:wrestler_names,id'],
        ];
    }

    /**
     * Ensure provided alias (if any) belongs to the route wrestler.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $aliasId = $this->input('wrestler_name_id_at_win');
            if (! $aliasId) {
                return;
            }

            /** @var Wrestler|null $wrestler */
            $wrestler = $this->route('wrestler');
            if (! $wrestler instanceof Wrestler) {
                $v->errors()->add('wrestler_name_id_at_win', 'Unable to resolve wrestler from route.');
                return;
            }

            $belongs = WrestlerName::query()
                ->whereKey($aliasId)
                ->where('wrestler_id', $wrestler->getKey())
                ->exists();

            if (! $belongs) {
                $v->errors()->add('wrestler_name_id_at_win', 'Alias does not belong to this wrestler.');
            }
        });
    }

    public function getChampionship(): ?Championship
    {
        return $this->resolvedChampionship;
    }
}
