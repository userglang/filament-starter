<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                    ->description('Enter the basic details for this branch.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        ...self::getBranchInformation(),
                        ...self::getStatusAndSettings(),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function getBranchInformation(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('branch_number')
                        ->label('Branch Number')
                        ->placeholder('e.g. BR-0001')
                        ->required()
                        ->maxLength(20)
                        ->helperText('Unique identifier for this branch.')
                        ->validationAttribute('branch number')
                        ->unique(Branch::class, 'branch_number', ignoreRecord: true)
                        ->alphaDash(),

                    TextInput::make('code')
                        ->label('Branch Code')
                        ->placeholder('e.g. MAIN')
                        ->required()
                        ->maxLength(10)
                        ->helperText('Short code used in reports and dropdowns.')
                        ->validationAttribute('branch code')
                        ->unique(Branch::class, 'code', ignoreRecord: true)
                        ->alphaDash()
                        ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                        ->dehydrateStateUsing(fn (string $state): string => strtoupper($state)),
                ]),

            TextInput::make('branch_name')
                ->label('Branch Name')
                ->placeholder('e.g. Main Branch')
                ->required()
                ->maxLength(100)
                ->helperText('Full descriptive name of the branch location.')
                ->validationAttribute('branch name')
                ->columnSpanFull(),

            Textarea::make('address')
                ->label('Address')
                ->placeholder('Enter complete address including street, city, and postal code…')
                ->maxLength(500)
                ->rows(3)
                ->helperText('Leave blank if the address is not yet available.')
                ->nullable()
                ->columnSpanFull(),
        ];
    }

    public static function getStatusAndSettings(): array
    {
        return [
            Section::make('Status & Settings')
                ->description('Configure the operational status of this branch.')
                ->icon('heroicon-o-cog-6-tooth')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->helperText('Inactive branches are hidden from listings and cannot be assigned to users.')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->onIcon('heroicon-s-check-circle')
                        ->offIcon('heroicon-s-x-circle')
                        ->inline(false),
                ]),
        ];
    }
}
