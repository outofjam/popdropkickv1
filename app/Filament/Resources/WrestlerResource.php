<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WrestlerResource\Pages;
use App\Filament\Resources\WrestlerResource\RelationManagers\ActivePromotionsRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\NamesRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\PromotionsRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\TitleReignsRelationManager;
use App\Helpers\FilamentHelpers;
use App\Models\Wrestler;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WrestlerResource extends Resource
{
    protected static ?string $model = Wrestler::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->disabled(),
                Forms\Components\TextInput::make('real_name'),
                Forms\Components\DatePicker::make('debut_date'),
                Forms\Components\TextInput::make('country'),
                FilamentHelpers::userDisplayField('created_by', 'Created By'),
                FilamentHelpers::userDisplayField('updated_by', 'Updated By'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('primaryName.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('real_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('debut_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                FilamentHelpers::userDisplayColumn('created_by', 'Created By')
                    ->searchable(false) // Disable search on this column
                    ->toggleable(isToggledHiddenByDefault: true),
                FilamentHelpers::userDisplayColumn('updated_by', 'Updated By')
                    ->searchable(false) // Disable search on this column
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PromotionsRelationManager::class,
            ActivePromotionsRelationManager::class,
            NamesRelationManager::class,
            TitleReignsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWrestlers::route('/'),
            'create' => Pages\CreateWrestler::route('/create'),
            'edit' => Pages\EditWrestler::route('/{record}/edit'),
        ];
    }


}
