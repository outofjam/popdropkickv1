<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use App\Filament\Actions\AttachPromotionAction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'promotions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Remove this - AttachAction handles it
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\ToggleColumn::make('is_active')
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
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
