<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PromotionResource\Pages\ListPromotions;
use App\Filament\Resources\PromotionResource\Pages\CreatePromotion;
use App\Filament\Resources\PromotionResource\Pages\EditPromotion;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers\ActiveChampionshipsRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\ActiveWrestlersRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\ChampionshipsRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\WrestlersRelationManager;
use App\Helpers\FilamentHelpers;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->disabled(),
                TextInput::make('abbreviation'),
                TextInput::make('country'),
                FilamentHelpers::userDisplayField('created_by', 'Created By'),
                FilamentHelpers::userDisplayField('updated_by', 'Updated By'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('abbreviation')
                    ->searchable(),
                TextColumn::make('country')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActiveChampionshipsRelationManager::class,
            ActiveWrestlersRelationManager::class,
            ChampionshipsRelationManager::class,
            WrestlersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
        ];
    }
}
