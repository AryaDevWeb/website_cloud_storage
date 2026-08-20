<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages;
use App\Models\Major;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Jurusan';
    protected static ?string $modelLabel = 'Jurusan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('short_name')->required()->maxLength(255),
            Textarea::make('description')->columnSpanFull(),
            Toggle::make('is_active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('short_name')->searchable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMajors::route('/'),
            'create' => Pages\CreateMajor::route('/create'),
            'edit' => Pages\EditMajor::route('/{record}/edit'),
        ];
    }
}
