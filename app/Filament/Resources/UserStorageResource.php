<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserStorageResource\Pages;
use App\Models\StorageQuota;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserStorageResource extends Resource
{
    protected static ?string $model = StorageQuota::class;
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Kuota Penyimpanan';
    protected static ?string $modelLabel = 'Kuota Penyimpanan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('max_bytes')->label('Maksimum Bytes')->numeric()->integer()->minValue(1)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('used_bytes')->label('Terpakai')->formatStateUsing(fn ($state) => self::formatBytes((int) $state)),
                TextColumn::make('max_bytes')->label('Maksimum')->formatStateUsing(fn ($state) => self::formatBytes((int) $state)),
                TextColumn::make('usage_percent')->label('Penggunaan')->state(function (StorageQuota $record): string {
                    $percent = $record->max_bytes > 0 ? ($record->used_bytes / $record->max_bytes) * 100 : 0;

                    return number_format(min(100, $percent), 2).'%';
                }),
            ])
            ->actions([EditAction::make()])
            ->defaultSort('used_bytes', 'desc');
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        return number_format($bytes / 1048576, 2).' MB';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserStorage::route('/'),
            'edit' => Pages\EditUserStorage::route('/{record}/edit'),
        ];
    }
}
