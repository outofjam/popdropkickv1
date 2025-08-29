<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Actions\AttachPromotionAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ActivePromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'activePromotions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Remove this - AttachAction doesn't need a custom form
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
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
            ->recordActions([
                DeleteAction::make()
                    ->action(function ($record) {
                        // Only remove from active promotions, keep in general promotions
                        $this->ownerRecord->activePromotions()->detach($record->id);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Only remove from active promotions, keep in general promotions
                            $recordIds = $records->pluck('id')->toArray();
                            $this->ownerRecord->activePromotions()->detach($recordIds);
                        }),
                ]),
            ]);
    }
}
