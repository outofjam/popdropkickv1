<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use App\Filament\Actions\AttachPromotionAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'promotions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Remove this - AttachAction handles it
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->getStateUsing(function ($record) {
                        return $this->ownerRecord->activePromotions()->where('promotion_id', $record->id)->exists();
                    })
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            // Add to active promotions
                            $this->ownerRecord->activePromotions()->syncWithoutDetaching([$record->id]);
                        } else {
                            // Remove from active promotions
                            $this->ownerRecord->activePromotions()->detach($record->id);
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachPromotionAction::make()->forPromotions(),
            ])
            ->recordActions([
//                Tables\Actions\EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
