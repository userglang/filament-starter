<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->record;

        $notification = match (true) {
            $record->wasChanged('is_active') && $record->is_active => [
                'icon'      => 'heroicon-o-lock-open',
                'iconColor' => 'success',
                'title'     => 'Account Activated',
                'body'      => "{$record->name}'s account is now active and can log in.",
            ],
            $record->wasChanged('is_active') && ! $record->is_active => [
                'icon'      => 'heroicon-o-lock-closed',
                'iconColor' => 'warning',
                'title'     => 'Account Deactivated',
                'body'      => "{$record->name}'s account has been disabled.",
            ],
            $record->wasChanged('branch_id') => [
                'icon'      => 'heroicon-o-building-office',
                'iconColor' => 'info',
                'title'     => 'Branch Updated',
                'body'      => "{$record->name} has been moved to a new branch.",
            ],
            $record->wasChanged('email') => [
                'icon'      => 'heroicon-o-envelope',
                'iconColor' => 'info',
                'title'     => 'Email Updated',
                'body'      => "{$record->name} should use their new address to log in.",
            ],
            default => [
                'icon'      => 'heroicon-o-check-circle',
                'iconColor' => 'success',
                'title'     => 'Changes Saved',
                'body'      => "{$record->name}'s profile has been updated successfully.",
            ],
        };

        return Notification::make()
            ->icon($notification['icon'])
            ->iconColor($notification['iconColor'])
            ->title($notification['title'])
            ->body($notification['body'])
            ->duration(5000);
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('back')
                ->label('Back')
                ->url(UserResource::getUrl('index'))
                ->color('warning')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->hidden();
    }
}
