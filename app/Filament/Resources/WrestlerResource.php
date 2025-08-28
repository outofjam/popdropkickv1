<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\WrestlerResource\Pages\ListWrestlers;
use App\Filament\Resources\WrestlerResource\Pages\CreateWrestler;
use App\Filament\Resources\WrestlerResource\Pages\EditWrestler;
use App\Filament\Resources\WrestlerResource\Pages;
use App\Filament\Resources\WrestlerResource\RelationManagers\ActivePromotionsRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\NamesRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\PromotionsRelationManager;
use App\Filament\Resources\WrestlerResource\RelationManagers\TitleReignsRelationManager;
use App\Helpers\FilamentHelpers;
use App\Models\Wrestler;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;


class WrestlerResource extends Resource
{
    protected static ?string $model = Wrestler::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->disabled(),
                TextInput::make('real_name'),
                DatePicker::make('debut_date'),
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('primaryName.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('real_name')
                    ->searchable(),
                TextColumn::make('debut_date')
                    ->date()
                    ->sortable(),
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
                FilamentHelpers::userDisplayColumn('created_by', 'Created By')
                    ->searchable(false) // Disable search on this column
                    ->toggleable(isToggledHiddenByDefault: true),
                FilamentHelpers::userDisplayColumn('updated_by', 'Updated By')
                    ->searchable(false) // Disable search on this column
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('names.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('All Names'),
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
            ])->modifyQueryUsing(function (Builder $query) {
                return $query->select(['id', 'slug', 'real_name', 'debut_date', 'country', 'created_at', 'updated_at'])
                    ->with(['primaryName:id,wrestler_id,name,is_primary']);
            })
            ->deferLoading() // Load table data only when needed
            ->searchable() // Enable global search
            ->searchOnBlur(); // Optional: search as you type vs on blur
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
            'index' => ListWrestlers::route('/'),
            'create' => CreateWrestler::route('/create'),
            'edit' => EditWrestler::route('/{record}/edit'),
        ];
    }


}
