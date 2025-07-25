<?php

namespace App\Filament\Resources\ChangeRequestResource\Pages;

<<<<<<< HEAD
use Filament\Actions\CreateAction;
=======
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
use App\Filament\Resources\ChangeRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChangeRequests extends ListRecords
{
    protected static string $resource = ChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
<<<<<<< HEAD
            CreateAction::make(),
=======
            Actions\CreateAction::make(),
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
        ];
    }
}
