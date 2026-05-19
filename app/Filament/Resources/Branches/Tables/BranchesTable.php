<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Models\Branch;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch_number')
                    ->label('BRN')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Branch number copied!')
                    ->tooltip('Click to copy branch number'),

                // Name + optional short code stacked — the main label for this row
                TextColumn::make('branch_name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Branch name copied!')
                    ->tooltip('Click to copy branch name')
                    ->wrap()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->code
                        ? 'Code: ' . strtoupper($record->code)
                        : null
                    ),

                // ── Staff Count ───────────────────────────────────────────────
                TextColumn::make('users_count')
                    ->label('Staff')
                    ->counts('users')
                    ->icon('heroicon-m-users')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 3  => 'warning',
                        default      => 'success',
                    })
                    ->tooltip(fn (int $state): string => match (true) {
                        $state === 0 => 'No staff assigned',
                        $state === 1 => '1 staff member',
                        default      => "{$state} staff members",
                    })
                    ->sortable(),

                // ── Address ───────────────────────────────────────────────────
                TextColumn::make('address')
                    ->label('Location')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Branch $record): ?string => $record->address)
                    ->placeholder('No address on file')
                    ->icon('heroicon-o-map-pin')
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('Address copied!'),

                // ── Active Status ─────────────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Branch $record): string => $record->is_active ? 'Active' : 'Inactive')
                    ->sortable()
                    ->alignCenter(),

                // Timestamps — hidden by default, available when needed
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->size('sm')
                    ->tooltip(fn ($record) => 'Created ' . $record->created_at->format('F j, Y g:i A')),

                TextColumn::make('updated_at')
                    ->label('Last Update')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color('gray')
                    ->size('sm')
                    ->tooltip(fn ($record) => 'Updated ' . $record->updated_at->format('F j, Y g:i A')),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All branches')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                SelectFilter::make('staff_count')
                    ->label('Staff')
                    ->options([
                        'none' => 'No staff assigned',
                        'low'  => 'Low (1–3)',
                        'ok'   => 'Sufficient (4+)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'none' => $query->doesntHave('users'),
                            'low'  => $query->has('users', '<=', 3)->has('users', '>=', 1),
                            'ok'   => $query->has('users', '>=', 4),
                            default => $query,
                        };
                    })
                    ->native(false),
            ])
            ->recordActions([
                // Toggle status inline — no need to open the edit form just for this
                Action::make('toggleStatus')
                    ->label(fn (Branch $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Branch $record): string => $record->is_active
                        ? 'heroicon-m-lock-closed'
                        : 'heroicon-m-lock-open'
                    )
                    ->color(fn (Branch $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalIcon(fn (Branch $record): string => $record->is_active
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open'
                    )
                    ->modalHeading(fn (Branch $record): string => $record->is_active
                        ? 'Deactivate Branch'
                        : 'Activate Branch'
                    )
                    ->modalDescription(fn (Branch $record): string => $record->is_active
                        ? "Deactivating \"{$record->branch_name}\" will hide it from active listings. You can re-activate it at any time."
                        : "Activating \"{$record->branch_name}\" will make it visible in active listings."
                    )
                    ->modalSubmitActionLabel(fn (Branch $record): string => $record->is_active
                        ? 'Yes, deactivate'
                        : 'Yes, activate'
                    )
                    ->action(function (Branch $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Branch Activated' : 'Branch Deactivated')
                            ->body("\"{$record->branch_name}\" has been " . ($record->is_active ? 'activated.' : 'deactivated.'))
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('Edit Branch')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'),
                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Delete')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Branch')
                        ->modalDescription('This will permanently remove the branch and all associated data. This cannot be undone.')
                        ->modalSubmitActionLabel('Yes, permanently delete')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Branch Deleted')
                                ->body('The branch has been permanently removed.')
                        ),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->tooltip('More actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Selected Branches')
                        ->modalDescription('The selected branches will become visible in active listings.')
                        ->modalSubmitActionLabel('Yes, activate all')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title('Branches Activated')
                                ->body($records->count() . ' ' . str('branch')->plural($records->count()) . ' activated successfully.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-s-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Selected Branches')
                        ->modalDescription('The selected branches will be hidden from active listings. You can re-activate them at any time.')
                        ->modalSubmitActionLabel('Yes, deactivate all')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->warning()
                                ->title('Branches Deactivated')
                                ->body($records->count() . ' ' . str('branch')->plural($records->count()) . ' deactivated.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Branches')
                        ->modalDescription('This will permanently remove all selected branches. This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete all selected')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Branches Deleted')
                                ->body('The selected branches have been permanently removed.')
                        )
                        ->deselectRecordsAfterCompletion(),
                ])
                ->label('Bulk Actions'),
            ])
            // ── Table behaviour ──────────────────────────────────────────────
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->deferLoading()
            ->searchOnBlur()
            ->searchDebounce('500ms')
            ->searchPlaceholder('Search by name, number, or address…')
            ->poll(null)
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->recordUrl(null)

            // ── Empty state ──────────────────────────────────────────────────
            ->emptyStateHeading('No branches yet')
            ->emptyStateDescription('Add your first branch location to get started.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add First Branch')
                    ->icon('heroicon-s-plus'),
            ]);
    }
}
