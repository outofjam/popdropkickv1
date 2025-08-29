<?php

namespace App\Filament\Resources;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use App\Filament\Resources\ChangeRequestResource\Pages\ListChangeRequests;
use App\Filament\Resources\ChangeRequestResource\Pages\ViewChangeRequest;
use App\Filament\Resources\ChangeRequestResource\Pages;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ChangeRequestResource extends Resource
{
    protected static ?string $model = ChangeRequest::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Change Requests';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Details')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Submitted By')
                            ->disabled(),
                        Select::make('model_type')
                            ->label('Content Type')
                            ->options([
                                'wrestler' => 'Wrestler',
                                'championship' => 'Championship',
                                'title_reign' => 'Title Reign',
                                'promotion' => 'Promotion',
                            ])
                            ->disabled(),
                        Select::make('action')
                            ->options([
                                'create' => 'Create',
                                'update' => 'Update',
                                'delete' => 'Delete',
                            ])
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(),
                        DateTimePicker::make('created_at')
                            ->label('Submitted At')
                            ->disabled(),
                    ])->columns(),

                Section::make('Proposed Changes')
                    ->schema([
                        Placeholder::make('changes')
                            ->label('')
                            ->content(static function (ChangeRequest $record): HtmlString {
                                return new HtmlString(self::formatChanges($record));
                            }),
                    ]),

                Section::make('Review')
                    ->schema([
                        Textarea::make('reviewer_comments')
                            ->label('Review Comments')
                            ->rows(3),
                        TextInput::make('reviewer.name')
                            ->label('Reviewed By')
                            ->disabled()
                            ->visible(static fn (ChangeRequest $record) => $record->reviewer_id !== null),
                        DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled()
                            ->visible(static fn (ChangeRequest $record) => $record->reviewed_at !== null),
                    ])
                    ->visible(static fn (ChangeRequest $record) => $record->status !== 'pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('model_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'wrestler',
                        'success' => 'championship',
                        'warning' => 'title_reign',
                        'info' => 'promotion',
                    ]),

                BadgeColumn::make('action')
                    ->colors([
                        'success' => 'create',
                        'warning' => 'update',
                        'danger' => 'delete',
                    ]),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),

                SelectFilter::make('model_type')
                    ->label('Content Type')
                    ->options([
                        'wrestler' => 'Wrestler',
                        'championship' => 'Championship',
                        'title_reign' => 'Title Reign',
                        'promotion' => 'Promotion',
                    ]),

                SelectFilter::make('action')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('comments')
                            ->label('Approval Comments (Optional)')
                            ->rows(2),
                    ])
                    ->action(function (ChangeRequest $record, array $data) {
                        try {
                            app(ChangeRequestService::class)->approve($record, $data);

                            Notification::make()
                                ->title('Change Request Approved')
                                ->success()
                                ->send();
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
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('comments')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (ChangeRequest $record, array $data) {
                        app(ChangeRequestService::class)->reject($record, $data);

                        Notification::make()
                            ->title('Change Request Rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('bulk_approve')
                    ->label('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Textarea::make('comments')
                            ->label('Bulk Approval Comments')
                            ->rows(2),
                    ])
                    ->action(function ($records, array $data) {
                        $approved = 0;
                        $errors = [];

                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                try {
                                    app(ChangeRequestService::class)->approve($record, $data);
                                    $approved++;
                                } catch (Exception $e) {
                                    $errors[] = "ID {$record->id}: " . $e->getMessage();
                                }
                            }
                        }

                        $message = "Approved {$approved} requests";
                        if (count($errors) > 0) {
                            $message .= '. Errors: ' . implode(', ', array_slice($errors, 0, 3));
                            if (count($errors) > 3) {
                                $message .= ' and ' . (count($errors) - 3) . ' more...';
                            }
                        }

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function formatChanges(ChangeRequest $record): string
    {
        if ($record->action === 'create') {
            return '<div class="space-y-2">' .
                collect($record->data)->map(static fn($value, $key) =>
                    "<div><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . htmlspecialchars($value ?? 'null') . "</div>"
                )->implode('') .
                '</div>';
        }

        if ($record->action === 'update' && $record->original_data) {
            $changes = [];
            foreach ($record->data as $key => $newValue) {
                $oldValue = $record->original_data[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $fieldName = ucfirst(str_replace('_', ' ', $key));
                    $changes[] = "<div class='mb-2'>
                        <strong>{$fieldName}:</strong><br>
                        <span class='text-red-600'>- " . htmlspecialchars($oldValue ?? 'null') . "</span><br>
                        <span class='text-green-600'>+ " . htmlspecialchars($newValue ?? 'null') . "</span>
                    </div>";
                }
            }
            return '<div class="space-y-2">' . implode('', $changes) . '</div>';
        }

        if ($record->action === 'delete') {
            return '<div class="text-red-600 font-semibold">This item will be permanently deleted.</div>';
        }

        return 'No changes to display';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChangeRequests::route('/'),
            'view' => ViewChangeRequest::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
