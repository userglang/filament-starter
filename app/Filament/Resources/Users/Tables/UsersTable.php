<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Branch;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ── Avatar ────────────────────────────────────────────────────
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => self::generateAvatarUrl($record->name))
                    ->size(36)
                    ->toggleable(isToggledHiddenByDefault: false),

                // ── Name + Email (stacked) ────────────────────────────────────
                TextColumn::make('name')
                    ->label('User')
                    ->description(fn (User $record): string => $record->branch->branch_name ?? 'No branch assignment!')
                    ->searchable(['name',  'branch.branch_name'])
                    ->sortable()
                    ->weight('semibold')
                    ->icon('heroicon-m-user')
                    ->copyable()
                    ->copyMessage('Name copied to clipboard!')
                    ->tooltip('Click to copy name'),

                // ── Branch ────────────────────────────────────────────────────
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied to clipboard!')
                    ->tooltip('Click to copy email')
                    ->placeholder('No email found'),

                // ── Email Verified ────────────────────────────────────────────
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null)
                    ->trueIcon('heroicon-s-check-badge')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn (User $record): string => $record->email_verified_at
                        ? 'Verified on ' . $record->email_verified_at->format('M j, Y')
                        : 'Email not yet verified'
                    )
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),

                // ── Active Status ─────────────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (User $record): string => $record->is_active ? 'Active' : 'Inactive')
                    ->alignCenter()
                    ->sortable(),

                // ── Timestamps ────────────────────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-calendar')
                    ->tooltip(fn (User $record): string => $record->created_at->format('F j, Y g:i A'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Update')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->size('sm')
                    ->icon('heroicon-m-clock')
                    ->tooltip(fn (User $record): string => $record->updated_at->format('F j, Y g:i A'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])

            // ── Filters ───────────────────────────────────────────────────────
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All users')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                TernaryFilter::make('email_verified_at')
                    ->label('Email Verification')
                    ->placeholder('All users')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    )
                    ->native(false),

                SelectFilter::make('branch_number')
                    ->label('Branch')
                    ->relationship('branch', 'branch_name')
                    ->getOptionLabelFromRecordUsing(fn (Branch $record): string =>
                        "[{$record->code}] {$record->branch_name}"
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('All branches')
                    ->native(false),
            ])
            ->filtersFormColumns(3)

            // ── Row Actions ───────────────────────────────────────────────────
            ->recordActions([

                Action::make('toggleStatus')
                    ->label(fn (User $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->tooltip(fn (User $record): string => $record->is_active
                        ? 'Deactivate this user account'
                        : 'Activate this user account'
                    )
                    ->icon(fn (User $record): string => $record->is_active
                        ? 'heroicon-m-lock-closed'
                        : 'heroicon-m-lock-open'
                    )
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalIcon(fn (User $record): string => $record->is_active
                        ? 'heroicon-o-lock-closed'
                        : 'heroicon-o-lock-open'
                    )
                    ->modalHeading(fn (User $record): string => $record->is_active
                        ? 'Deactivate User'
                        : 'Activate User'
                    )
                    ->modalDescription(fn (User $record): string => $record->is_active
                        ? "Deactivating \"{$record->name}\" will prevent them from logging in."
                        : "Activating \"{$record->name}\" will restore their access to the system."
                    )
                    ->modalSubmitActionLabel(fn (User $record): string => $record->is_active
                        ? 'Yes, deactivate'
                        : 'Yes, activate'
                    )
                    ->action(function (User $record): void {
                        $record->update([
                            'is_active' => ! $record->is_active,
                        ]);

                        Notification::make()
                            ->title($record->is_active ? 'User Activated' : 'User Deactivated')
                            ->body("\"{$record->name}\" has been " . ($record->is_active ? 'activated.' : 'deactivated.'))
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Edit Account')
                    ->tooltip('Edit this user account')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'),

                ActionGroup::make([

                    Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-m-key')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Password')
                        ->modalDescription('Are you sure you want to reset the password for this user? It will be set to the default: password123.')
                        ->modalSubmitActionLabel('Yes, reset')
                        // ->visible(fn ($record) => Auth::user()->can('update_user', $record))
                        ->action(function ($record) {
                            $record->update([
                                'password' => Hash::make('password123'),
                            ]);
                            // Clear middleware caches so checks re-run immediately
                            Cache::forget("password_expired_check:{$record->id}");
                            // default_password_check auto-invalidates via password fingerprint — no forget needed

                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Password Reset')
                                ->body('The password has been reset to the default: password123.')
                        ),
                    Action::make('resendVerification')
                        ->label('Resend Verification Email')
                        ->tooltip('Send a new verification email')
                        ->icon('heroicon-m-envelope')
                        ->color('info')
                        ->visible(fn (User $record): bool => $record->email_verified_at === null)
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-envelope')
                        ->modalHeading('Resend Verification Email')
                        ->modalDescription(fn (User $record): string =>
                            "A new verification email will be sent to {$record->email}."
                        )
                        ->modalSubmitActionLabel('Yes, resend')
                        ->action(function (User $record): void {
                            $record->sendEmailVerificationNotification();

                            Notification::make()
                                ->success()
                                ->title('Verification Email Sent')
                                ->body("Verification email sent to {$record->email}.")
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Delete User')
                        ->tooltip('Permanently delete this user')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-trash')
                        ->modalHeading('Delete User')
                        ->modalDescription(fn (User $record): string =>
                            "This will permanently remove \"{$record->name}\". This cannot be undone."
                        )
                        ->modalSubmitActionLabel('Yes, permanently delete')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('User Deleted')
                                ->body('The user has been permanently removed.')
                        ),
                ])
                    ->label('More')
                    ->tooltip('More user actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])

            // ── Bulk Actions ──────────────────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-check-circle')
                        ->modalHeading('Activate Selected Users')
                        ->modalDescription('Selected users will be able to log in to the system.')
                        ->modalSubmitActionLabel('Yes, activate all')
                        ->action(function ($records): void {
                            $count = $records->count();
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title('Users Activated')
                                ->body("{$count} " . str('user')->plural($count) . ' activated successfully.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-s-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-x-circle')
                        ->modalHeading('Deactivate Selected Users')
                        ->modalDescription('Selected users will no longer be able to log in to the system.')
                        ->modalSubmitActionLabel('Yes, deactivate all')
                        ->action(function ($records): void {
                            $count = $records->count();
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->warning()
                                ->title('Users Deactivated')
                                ->body("{$count} " . str('user')->plural($count) . ' deactivated.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-trash')
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('This will permanently remove all selected users. This cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete all selected')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Users Deleted')
                                ->body('The selected users have been permanently removed.')
                        )
                        ->deselectRecordsAfterCompletion(),
                ])
                ->label('Bulk Actions'),
            ])

            // ── Table Behaviour ───────────────────────────────────────────────
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->deferLoading()
            ->searchOnBlur()
            ->searchDebounce('400ms')
            ->searchPlaceholder('Search by name or email…')
            ->poll(null)
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->recordUrl(null)
            ->columnToggleFormColumns(2)

            // ── Empty State ───────────────────────────────────────────────────
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Add your first user or adjust your filters.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add First User')
                    ->icon('heroicon-s-plus'),
            ]);
    }

    private static function generateAvatarUrl(string $name): string
    {
        return 'https://ui-avatars.com/api/?' . http_build_query([
            'name'       => $name,
            'background' => 'random',
            'color'      => 'fff',
            'bold'       => 'true',
            'format'     => 'svg',
        ]);
    }
}
