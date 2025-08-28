<?php

namespace App\Filament\Resources\ChangeRequestResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\EditAction;
use App\Filament\Resources\ChangeRequestResource;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use Exception;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewChangeRequest extends ViewRecord
{
    protected static string $resource = ChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->schema([
                    Textarea::make('comments')
                        ->label('Approval Comments (Optional)')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        /** @var ChangeRequest $record */
                        $record = $this->record;
                        app(ChangeRequestService::class)->approve($record, $data);

                        Notification::make()
                            ->title('Change Request Approved')
                            ->success()
                            ->send();

                        return redirect()->to(ChangeRequestResource::getUrl());
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Approval Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->schema([
                    Textarea::make('comments')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    /** @var ChangeRequest $record */
                    $record = $this->record;
                    app(ChangeRequestService::class)->reject($record, $data);

                    Notification::make()
                        ->title('Change Request Rejected')
                        ->success()
                        ->send();

                    return redirect()->to(ChangeRequestResource::getUrl('index'));
                }),

            EditAction::make()
                ->visible(false), // Hide edit action since we handle approve/reject differently
        ];
    }
}
