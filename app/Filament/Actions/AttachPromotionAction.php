<?php

namespace App\Filament\Actions;

use Filament\Actions\AttachAction;
use Filament\Forms\Components\Select;
use App\Models\Promotion;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class AttachPromotionAction extends AttachAction
{
    protected string $relationshipType = 'promotions'; // default

    public static function getDefaultName(): ?string
    {
        return 'attachPromotion';
    }

    public function forActivePromotions(): static
    {
        $this->relationshipType = 'activePromotions';
        return $this;
    }

    public function forPromotions(): static
    {
        $this->relationshipType = 'promotions';
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordSelectOptionsQuery(function (Builder $query) {
            $table = $this->relationshipType === 'activePromotions'
                ? 'active_promotion_wrestler'
                : 'promotion_wrestler';

            return $query->whereNotIn('id', function ($subquery) use ($table) {
                $subquery->select('promotion_id')
                    ->from($table)
                    ->where('wrestler_id', $this->getLivewire()->getOwnerRecord()->id);
            })->orderBy('name');
        })
            ->form([
                Select::make('recordId')
                    ->label('Promotion')
                    ->searchable()
                    ->options(function () {
                        $table = $this->relationshipType === 'activePromotions'
                            ? 'active_promotion_wrestler'
                            : 'promotion_wrestler';

                        return Promotion::whereNotIn('id', function ($subquery) use ($table) {
                            $subquery->select('promotion_id')
                                ->from($table)
                                ->where('wrestler_id', $this->getLivewire()->getOwnerRecord()->id);
                        })
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(function ($promotion) {
                                return [$promotion->id => $promotion->name . ($promotion->abbreviation ? " ({$promotion->abbreviation})" : '')];
                            });
                    })
                    ->getSearchResultsUsing(function (string $search) {
                        $table = $this->relationshipType === 'activePromotions'
                            ? 'active_promotion_wrestler'
                            : 'promotion_wrestler';

                        return Promotion::whereNotIn('id', function ($subquery) use ($table) {
                            $subquery->select('promotion_id')
                                ->from($table)
                                ->where('wrestler_id', $this->getLivewire()->getOwnerRecord()->id);
                        })
                            ->where(function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('abbreviation', 'like', "%{$search}%");
                            })
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(static function ($promotion) {
                                return [$promotion->id => $promotion->name . ($promotion->abbreviation ? " ({$promotion->abbreviation})" : '')];
                            });
                    })
                    ->required(),
            ]);
    }
}
