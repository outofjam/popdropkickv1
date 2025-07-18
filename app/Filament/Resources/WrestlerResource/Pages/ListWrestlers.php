<?php

namespace App\Filament\Resources\WrestlerResource\Pages;

use App\Filament\Resources\WrestlerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWrestlers extends ListRecords
{
    protected static string $resource = WrestlerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
