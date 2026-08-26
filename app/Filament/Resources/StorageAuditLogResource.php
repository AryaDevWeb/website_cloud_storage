<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageAuditLogResource\Pages;
use App\Models\StorageAuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StorageAuditLogResource extends Resource
{
    protected static ?string $model = StorageAuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Storage Audit Logs';
    protected static ?string $modelLabel = 'Storage Audit Log';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable(),
                TextColumn::make('media.file_name')->label('File')->searchable(),
                TextColumn::make('action')->badge()->searchable(),
                TextColumn::make('ip_address')->label('IP Address')->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')->options([
                    'upload' => 'Upload', 'download' => 'Download', 'share' => 'Share',
                    'delete' => 'Delete', 'restore' => 'Restore',
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStorageAuditLogs::route('/')];
    }
}
