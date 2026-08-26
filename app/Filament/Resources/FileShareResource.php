<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileShareResource\Pages;
use App\Models\FileShare;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FileShareResource extends Resource
{
    protected static ?string $model = FileShare::class;
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationLabel = 'Shared Files';
    protected static ?string $modelLabel = 'File Share';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('media.file_name')->label('File')->searchable(),
                TextColumn::make('sharedBy.email')->label('Dibagikan Oleh')->searchable(),
                TextColumn::make('sharedTo.email')->label('Penerima User')->searchable(),
                TextColumn::make('shared_to_role')->label('Penerima Role'),
                TextColumn::make('permission')->badge(),
                TextColumn::make('expires_at')->dateTime()->placeholder('Tidak kedaluwarsa')->sortable(),
            ])
            ->filters([
                SelectFilter::make('permission')->options(['view' => 'View', 'download' => 'Download']),
            ])
            ->actions([DeleteAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListFileShares::route('/')];
    }
}
