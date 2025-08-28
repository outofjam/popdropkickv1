<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NamesRelationManager extends RelationManager
{
    protected static string $relationship = 'names';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_primary')
                    ->label('Primary')
                    ->disabled(static function ($record) {
                        // Disable if this is currently the primary name
                        return (bool)$record?->is_primary;
                    })
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state && $record) {
                            // If setting this to primary, unset all other primary names
                            $this->ownerRecord->names()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data) {
                        if ($data['is_primary'] ?? false) {
                            // If creating a new primary name, unset existing primary
                            $this->ownerRecord->names()->update(['is_primary' => false]);
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data, $record) {
                        if (($data['is_primary'] ?? false) && !$record->is_primary) {
                            // If setting this to primary, unset all other primary names
                            $this->ownerRecord->names()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
