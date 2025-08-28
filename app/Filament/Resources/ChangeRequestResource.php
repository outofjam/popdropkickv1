<?php

namespace App\Filament\Resources;

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
<<<<<<< HEAD
=======
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
use App\Filament\Resources\ChangeRequestResource\Pages;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
<<<<<<< HEAD
<<<<<<< HEAD
use Filament\Schemas\Schema;
=======
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
use Filament\Schemas\Schema;
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ChangeRequestResource extends Resource
{
    protected static ?string $model = ChangeRequest::class;
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
=======
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
<<<<<<< HEAD
                        Forms\Components\Select::make('model_type')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                        Select::make('model_type')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                            ->label('Content Type')
                            ->options([
                                'wrestler' => 'Wrestler',
                                'championship' => 'Championship',
                                'title_reign' => 'Title Reign',
                                'promotion' => 'Promotion',
                            ])
                            ->disabled(),
<<<<<<< HEAD
<<<<<<< HEAD
                        Select::make('action')
=======
                        Forms\Components\Select::make('action')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                        Select::make('action')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                            ->options([
                                'create' => 'Create',
                                'update' => 'Update',
                                'delete' => 'Delete',
                            ])
                            ->disabled(),
<<<<<<< HEAD
<<<<<<< HEAD
                        Select::make('status')
=======
                        Forms\Components\Select::make('status')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                        Select::make('status')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(),
<<<<<<< HEAD
<<<<<<< HEAD
                        DateTimePicker::make('created_at')
=======
                        Forms\Components\DateTimePicker::make('created_at')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                        DateTimePicker::make('created_at')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                            ->label('Submitted At')
                            ->disabled(),
                    ])->columns(),

<<<<<<< HEAD
<<<<<<< HEAD
                Section::make('Proposed Changes')
                    ->schema([
                        Placeholder::make('changes')
=======
                Forms\Components\Section::make('Proposed Changes')
                    ->schema([
                        Forms\Components\Placeholder::make('changes')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                Section::make('Proposed Changes')
                    ->schema([
                        Placeholder::make('changes')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                            ->label('')
                            ->content(static function (ChangeRequest $record): HtmlString {
                                return new HtmlString(self::formatChanges($record));
                            }),
                    ]),

<<<<<<< HEAD
<<<<<<< HEAD
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
=======
                Forms\Components\Section::make('Review')
=======
                Section::make('Review')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->schema([
                        Textarea::make('reviewer_comments')
                            ->label('Review Comments')
                            ->rows(3),
                        TextInput::make('reviewer.name')
                            ->label('Reviewed By')
                            ->disabled()
                            ->visible(static fn (ChangeRequest $record) => $record->reviewer_id !== null),
<<<<<<< HEAD
                        Forms\Components\DateTimePicker::make('reviewed_at')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                        DateTimePicker::make('reviewed_at')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
<<<<<<< HEAD
<<<<<<< HEAD
                TextColumn::make('user.name')
=======
                Tables\Columns\TextColumn::make('user.name')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                TextColumn::make('user.name')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable(),

<<<<<<< HEAD
<<<<<<< HEAD
                BadgeColumn::make('model_type')
=======
                Tables\Columns\BadgeColumn::make('model_type')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                BadgeColumn::make('model_type')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->label('Type')
                    ->colors([
                        'primary' => 'wrestler',
                        'success' => 'championship',
                        'warning' => 'title_reign',
                        'info' => 'promotion',
                    ]),

<<<<<<< HEAD
<<<<<<< HEAD
                BadgeColumn::make('action')
=======
                Tables\Columns\BadgeColumn::make('action')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                BadgeColumn::make('action')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->colors([
                        'success' => 'create',
                        'warning' => 'update',
                        'danger' => 'delete',
                    ]),

<<<<<<< HEAD
<<<<<<< HEAD
                BadgeColumn::make('status')
=======
                Tables\Columns\BadgeColumn::make('status')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                BadgeColumn::make('status')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

<<<<<<< HEAD
<<<<<<< HEAD
                TextColumn::make('created_at')
=======
                Tables\Columns\TextColumn::make('created_at')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                TextColumn::make('created_at')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),

<<<<<<< HEAD
<<<<<<< HEAD
                TextColumn::make('reviewer.name')
=======
                Tables\Columns\TextColumn::make('reviewer.name')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                TextColumn::make('reviewer.name')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->label('Reviewed By')
                    ->placeholder('—'),
            ])
            ->filters([
<<<<<<< HEAD
<<<<<<< HEAD
                SelectFilter::make('status')
=======
                Tables\Filters\SelectFilter::make('status')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                SelectFilter::make('status')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),

<<<<<<< HEAD
<<<<<<< HEAD
                SelectFilter::make('model_type')
=======
                Tables\Filters\SelectFilter::make('model_type')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                SelectFilter::make('model_type')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->label('Content Type')
                    ->options([
                        'wrestler' => 'Wrestler',
                        'championship' => 'Championship',
                        'title_reign' => 'Title Reign',
                        'promotion' => 'Promotion',
                    ]),

<<<<<<< HEAD
<<<<<<< HEAD
                SelectFilter::make('action')
=======
                Tables\Filters\SelectFilter::make('action')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                SelectFilter::make('action')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),
            ])
<<<<<<< HEAD
<<<<<<< HEAD
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('comments')
=======
            ->actions([
                Tables\Actions\ViewAction::make(),
=======
            ->recordActions([
                ViewAction::make(),
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
<<<<<<< HEAD
                    ->form([
                        Forms\Components\Textarea::make('comments')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                    ->schema([
                        Textarea::make('comments')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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

<<<<<<< HEAD
<<<<<<< HEAD
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('comments')
=======
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('comments')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(static fn (ChangeRequest $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('comments')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
<<<<<<< HEAD
<<<<<<< HEAD
            ->toolbarActions([
                BulkAction::make('bulk_approve')
                    ->label('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Textarea::make('comments')
=======
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_approve')
                    ->label('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('comments')
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
            ->toolbarActions([
                BulkAction::make('bulk_approve')
                    ->label('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->schema([
                        Textarea::make('comments')
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
<<<<<<< HEAD
<<<<<<< HEAD
            'index' => ListChangeRequests::route('/'),
            'view' => ViewChangeRequest::route('/{record}'),
=======
            'index' => Pages\ListChangeRequests::route('/'),
            'view' => Pages\ViewChangeRequest::route('/{record}'),
>>>>>>> 1a81b22 (🎉 Add complete approval system with Filament admin dashboard)
=======
            'index' => ListChangeRequests::route('/'),
            'view' => ViewChangeRequest::route('/{record}'),
>>>>>>> e98d400 (upgrade this branch to v4 because it has changerequest related filament components)
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
