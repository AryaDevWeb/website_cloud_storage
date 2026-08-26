<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecycleBinResource\Pages;
use App\Models\Media;
use App\Services\StorageService;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecycleBinResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'Recycle Bin';
    protected static ?string $modelLabel = 'File Terhapus';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('file_name')->label('File')->searchable(),
                TextColumn::make('model_id')->label('Pemilik'),
                TextColumn::make('size')->formatStateUsing(fn ($state) => number_format((int) $state).' bytes'),
                TextColumn::make('deleted_at')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('restore')
                    ->label('Restore')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Media $record) => app(StorageService::class)->restore($record)),
                Action::make('forceDelete')
                    ->label('Hapus Permanen')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Media $record) => app(StorageService::class)->permanentlyDelete($record)),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->onlyTrashed();
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecycleBin::route('/')];
    }
}
