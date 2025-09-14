<?php

namespace App\Filament\Resources\WrestlerResource\RelationManagers;

use App\Models\Wrestler;
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
        /** @var Wrestler $owner */
        $owner = $this->getOwnerRecord();
        $aliasOptions = $owner->names()
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $primaryAliasId = $owner->primaryName()->value('id');

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
                Select::make('wrestler_name_id_at_win')
                    ->label('Alias at win')
                    ->options($aliasOptions)
                    ->searchable()
                    ->default($primaryAliasId)   // pre-fill with primary alias
                    ->required()
                    ->afterStateHydrated(function (Select $component, $state) {
                        if (blank($state)) {
                            $primaryId = $this->getOwnerRecord()->primaryName()->value('id');
                            if ($primaryId) {
                                $component->state($primaryId);
                            }
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $q) {
                $q->with('championship:id,name,slug')
                    ->with([
                        'aliasAtWin' => static function ($aq) {
                            $aq->select('id', 'name', 'wrestler_id')
                                ->with(['wrestler' => static fn($wq) => $wq->select('id', 'slug')]);
                        },
                        // eager-load fallback path used by the accessor to avoid N+1:
                        'wrestler:id,slug',
                        'wrestler.primaryName:id,wrestler_id,name',
                    ]);
            })
            ->columns([
                TextColumn::make('championship.name')->label('Championship'),
                // ✅ use the accessor that falls back to primary name
                TextColumn::make('resolved_display_name_at_win')
                    ->label('Alias at win')
                    ->badge(),
                TextColumn::make('reign_number')->label('Reign number'),
                TextColumn::make('won_on')->dateTime('Y-m-d H:i:s')->label('Won on'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

}
