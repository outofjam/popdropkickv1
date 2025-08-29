<?php

namespace App\Filament\Resources\ChangeRequestResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListChangeRequests extends ListRecords
{
    protected static string $resource = ChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
