<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChampionshipResource\Pages;

use App\Helpers\FilamentHelpers;
use App\Models\Championship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChampionshipResource extends Resource
{
    protected static ?string $model = Championship::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\Select::make('promotion_id')
                    ->relationship('promotion', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('abbreviation'),
                Forms\Components\TextInput::make('division'),
                Forms\Components\DatePicker::make('introduced_at'),
                Forms\Components\TextInput::make('weight_class'),
                Forms\Components\Toggle::make('active')
                    ->required(),
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
                Tables\Columns\TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('promotion.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division')
                    ->searchable(),
                Tables\Columns\TextColumn::make('introduced_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('weight_class')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                FilamentHelpers::userDisplayColumn('created_by', 'Created By')->toggleable(
                    isToggledHiddenByDefault: true
                ),
                FilamentHelpers::userDisplayColumn('updated_by', 'Updated By')->toggleable(
                    isToggledHiddenByDefault: true
                ),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChampionships::route('/'),
            'create' => Pages\CreateChampionship::route('/create'),
            'edit' => Pages\EditChampionship::route('/{record}/edit'),
        ];
    }
}
