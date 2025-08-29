<?php

namespace App\Filament\Resources\ChangeRequestResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ChangeRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditChangeRequest extends EditRecord
{
    protected static string $resource = ChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
