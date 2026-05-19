<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Branch;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->description('Enter the basic details for this user.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        ...self::getUserInformation(),
                        ...self::getPasswordSection(),
                        ...self::getStatusAndSettings(),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function getUserInformation(): array
    {
        return [
            Section::make('Personal Information')
                ->description('Basic details for this user.')
                ->icon('heroicon-m-user-circle')
                ->schema([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->placeholder('e.g. Juan dela Cruz')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Enter the full name as it should appear across the system.')
                        ->prefixIcon('heroicon-m-user')
                        ->autofocus(),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->placeholder('e.g. juan@company.com')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Used for login and system notifications.')
                        ->prefixIcon('heroicon-m-envelope')
                        ->maxLength(255),
                ])
                ->columnSpan('full')
                ->columns(2),
        ];
    }

    public static function getPasswordSection(): array
    {
        return [
            Section::make('Security & Access')
                ->description('Configure login credentials for this account.')
                ->icon('heroicon-m-lock-closed')
                ->schema([
                    // ── Create mode: set initial password ───────────────────
                    TextInput::make('password')
                        ->label('Initial Password')
                        ->placeholder('Minimum 8 characters')
                        ->password()
                        ->default('password123')
                        ->revealable()
                        ->required(fn ($livewire) => $livewire instanceof CreateUser)
                        ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->maxLength(255)
                        ->minLength(8)
                        ->helperText('The user can change this after their first login.')
                        ->prefixIcon('heroicon-m-key')
                        ->hiddenOn('edit')
                        ->suffixActions([
                            Action::make('generatePassword')
                                ->label('Generate')
                                ->icon('heroicon-m-arrow-path')
                                ->tooltip('Generate a random secure password')
                                ->action(function ($set) {
                                    $password = self::generateSecurePassword();
                                    $set('password', $password);
                                }),
                        ]),

                    // ── Edit mode: informational placeholder ─────────────────
                    Placeholder::make('password_change_note')
                        ->label('Password Management')
                        ->content('Passwords cannot be edited here. Use the "Reset Password" action on the user list, or ask the user to use the Forgot Password flow.')
                        ->visibleOn('edit'),
                ])
                ->columnSpan('full')
                ->columns(1),
        ];
    }

    public static function getStatusAndSettings(): array
    {
        return [
            Section::make('Work Assignment')
                ->description('Assign this user to a branch and configure their account status.')
                ->icon('heroicon-m-building-office-2')
                ->schema([
                    Select::make('branch_id')
                        ->label('Branch / Location')
                        ->placeholder('Select a branch…')
                        ->relationship('branch', 'branch_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('The primary branch where this person is based.')
                        ->prefixIcon('heroicon-m-building-office')
                        ->native(false),

                    // Select::make('roles')
                    //     ->label('Role')
                    //     ->placeholder('Select a role…')
                    //     ->relationship('roles', 'name')
                    //     ->searchable()
                    //     ->preload()
                    //     ->multiple()
                    //     ->helperText('Determines what this user can see and do in the system.')
                    //     ->prefixIcon('heroicon-m-shield-check')
                    //     ->native(false),

                    Toggle::make('is_active')
                        ->label('Active Account')
                        ->helperText('Inactive accounts cannot log in. You can change this at any time.')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->onIcon('heroicon-m-check-circle')
                        ->offIcon('heroicon-m-x-circle')
                        ->columnSpanFull(),
                ])
                ->columnSpan('full')
                ->columns(2),
        ];
    }

    /**
     * Generate a memorable but secure random password.
     * Pattern: Word + Number + Symbol (e.g. "Tiger@492")
     */
    private static function generateSecurePassword(): string
    {
        $words   = ['Tiger', 'Falcon', 'Eagle', 'Storm', 'River', 'Spark', 'Blaze', 'Cedar'];
        $symbols = ['@', '#', '!', '$', '%'];

        return $words[array_rand($words)]
            . $symbols[array_rand($symbols)]
            . random_int(100, 999);
    }
}
