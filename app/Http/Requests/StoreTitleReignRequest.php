<?php

namespace App\Http\Requests;

use App\Enums\WinType;
use App\Models\Championship;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTitleReignRequest extends FormRequest
{
    protected Championship $resolvedChampionship;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $input = $this->input('championship_id');

        $championship = Championship::where('id', $input)
            ->orWhere('slug', $input)
            ->firstOrFail();

        // Replace championship_id with its actual ID so the validation passes
        $this->merge([
            'championship_id' => $championship->id,
        ]);

        // Store resolved model for later access
        $this->resolvedChampionship = $championship;
    }

    /**
     * Retrieve the resolved Championship model.
     */
    public function getChampionship(): Championship
    {
        return $this->resolvedChampionship;
    }

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
            'championship_id' => 'required|exists:championships,id',
            'won_on' => 'required|date',
            'won_at' => 'nullable|string|max:255',
            'lost_on' => 'nullable|date|after_or_equal:won_on',
            'lost_at' => 'nullable|string|max:255',
            'win_type' => ['required', new Enum(WinType::class)],
            'wrestler_name_id_at_win' => 'nullable|exists:wrestler_names,id',
        ];
    }

    // Ensure alias belongs to route wrestler
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $aliasId = $this->input('wrestler_name_id_at_win');
            if (!$aliasId) {
                return;
            }

            $param = $this->route('wrestler');
            $wrestlerId = $param instanceof Wrestler ? $param->getKey() : (is_string($param) ? $param : null);
            if (!$wrestlerId) {
                $v->errors()->add('wrestler_name_id_at_win', 'Unable to resolve wrestler from route.');
                return;
            }

            $belongs = WrestlerName::whereKey($aliasId)
                ->where('wrestler_id', $wrestlerId)
                ->exists();

            if (!$belongs) {
                $v->errors()->add('wrestler_name_id_at_win', 'Alias does not belong to this wrestler.');
            }
        });
    }

}
