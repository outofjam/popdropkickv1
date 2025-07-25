<?php

namespace App\Filament\Resources\ChangeRequestResource\Pages;

<<<<<<< HEAD
use Filament\Actions\DeleteAction;
=======
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
use App\Filament\Resources\ChangeRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChangeRequest extends EditRecord
{
    protected static string $resource = ChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
<<<<<<< HEAD
            DeleteAction::make(),
=======
            Actions\DeleteAction::make(),
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
        ];
    }
}
