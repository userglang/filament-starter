<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;

        return Notification::make()
            ->icon('heroicon-o-user-plus')
            ->iconColor('success')
            ->title('Account Created')
            ->body("Welcome {$record->name}! Their account is ready and login credentials can now be shared.")
            ->duration(6000);
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
