<?php

namespace App\Filament\Resources\WrestlerResource\Pages;

use App\Filament\Resources\WrestlerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWrestler extends EditRecord
{
    protected static string $resource = WrestlerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
