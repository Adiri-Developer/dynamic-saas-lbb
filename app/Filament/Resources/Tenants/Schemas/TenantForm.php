<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('domain')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                KeyValue::make('settings')
                    ->columnSpanFull(),
            ]);
    }
}
