<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NamesRelationManager extends RelationManager
{
    protected static string $relationship = 'names';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_primary')
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
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        if ($data['is_primary'] ?? false) {
                            // If creating a new primary name, unset existing primary
                            $this->ownerRecord->names()->update(['is_primary' => false]);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record) {
                        if (($data['is_primary'] ?? false) && !$record->is_primary) {
                            // If setting this to primary, unset all other primary names
                            $this->ownerRecord->names()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
