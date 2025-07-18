<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use App\Filament\Actions\AttachPromotionAction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivePromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'activePromotions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Remove this - AttachAction doesn't need a custom form
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachPromotionAction::make()
                    ->forActivePromotions()
                    ->after(function ($record) {
                        // Also add to general promotions
                        $this->ownerRecord->promotions()->syncWithoutDetaching([$record->id]);
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        // Only remove from active promotions, keep in general promotions
                        $this->ownerRecord->activePromotions()->detach($record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Only remove from active promotions, keep in general promotions
                            $recordIds = $records->pluck('id')->toArray();
                            $this->ownerRecord->activePromotions()->detach($recordIds);
                        }),
                ]),
            ]);
    }
}
