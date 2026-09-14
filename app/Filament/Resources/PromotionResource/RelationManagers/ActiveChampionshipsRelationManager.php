<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Models\Championship;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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
            ->modifyQueryUsing(fn ($query) => $query->with([
                'currentTitleReign.team:id,name',
                'currentTitleReign.titleReignWrestlers.wrestler.primaryName',
                'currentTitleReign.wrestler.primaryName',
            ])
            )
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                // One or more current champs (or "Vacant")
                TextColumn::make('current_champions')
                    ->label('Current Champion(s)')
                    ->state(function ($record) {
                        $reign = $record->currentTitleReign;

                        if (! $reign) {
                            return 'Vacant';
                        }

                        $names = $reign->titleReignWrestlers->isNotEmpty()
                            ? $reign->titleReignWrestlers->map(fn ($p) => $p->resolved_display_name)->filter()
                            : collect([$reign->wrestler?->primaryName?->name])->filter();

                        if ($names->isEmpty()) {
                            return 'Vacant';
                        }

                        $names = $reign->team ? $names->push("({$reign->team->name})") : $names;

                        return $names->implode(', ');
                    }),
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
