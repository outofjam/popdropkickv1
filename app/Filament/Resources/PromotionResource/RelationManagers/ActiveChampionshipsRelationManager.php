<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Championship;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ActiveChampionshipsRelationManager extends RelationManager
{
    protected static string $relationship = 'activeChampionships';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(
                Championship::getForm()
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['currentTitleReign.wrestler.primaryName'])
            )
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                // Single current champ (or "Vacant")
                TextColumn::make('currentTitleReign.wrestler.primaryName.name')
                    ->label('Current Champion')
                    ->formatStateUsing(fn ($state, $record) =>
                    $record->currentTitleReign
                        ? $record->currentTitleReign->wrestler->primaryName->name
                        : 'Vacant'
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
