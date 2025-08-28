<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Wrestler;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WrestlersRelationManager extends RelationManager
{
    protected static string $relationship = 'wrestlers';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('name')
                    ->required()
                    ->options(Wrestler::all()->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with('primaryName'))
            ->recordTitleAttribute('slug')
            ->columns([
                TextColumn::make('slug'),
                TextColumn::make('name')->label('Primary Name'),
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
