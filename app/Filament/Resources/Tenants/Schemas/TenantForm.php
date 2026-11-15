<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
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
                    ->label('Nama Lembaga (LBB)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Subdomain / Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('domain')
                    ->label('Custom Domain')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                TextInput::make('phone')
                    ->label('WhatsApp / Telepon')
                    ->tel()
                    ->maxLength(50)
                    ->nullable(),
                Textarea::make('address')
                    ->label('Alamat Lembaga')
                    ->rows(2)
                    ->columnSpanFull()
                    ->nullable(),
                Textarea::make('description')
                    ->label('Deskripsi / Profil Lembaga')
                    ->rows(3)
                    ->columnSpanFull()
                    ->nullable(),
                FileUpload::make('logo_path')
                    ->label('Logo Lembaga')
                    ->image()
                    ->directory('logos')
                    ->visibility('public')
                    ->columnSpanFull()
                    ->nullable(),
                ColorPicker::make('primary_color')
                    ->label('Warna Branding')
                    ->default('#2563eb'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
                KeyValue::make('settings')
                    ->columnSpanFull(),
            ]);
    }
}
