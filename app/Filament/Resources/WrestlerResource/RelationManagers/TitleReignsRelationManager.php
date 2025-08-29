<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TitleReignsRelationManager extends RelationManager
{
    protected static string $relationship = 'titleReigns';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('championship_id')
                    ->label('Championship')
                    ->relationship('championship', 'name')
                    ->searchable()
                    ->required(),
                DatePicker::make('won_on'),
                DatePicker::make('lost_on'),
                TextInput::make('won_at'),
                TextInput::make('lost_at'),
                TextInput::make('reign_number')->numeric()->readonly()->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with('championship'))
            ->recordTitleAttribute('id')
            ->columns([
//                Tables\Columns\TextColumn::make('id'),
                TextColumn::make('championship.name'),
                TextColumn::make('reign_number'),
                TextColumn::make('won_on'),
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
