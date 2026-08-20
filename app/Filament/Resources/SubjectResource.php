<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?string $modelLabel = 'Mata Pelajaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(255),
            Select::make('type')->options([
                'core' => 'Umum',
                'specialization' => 'Kejuruan',
                'local' => 'Muatan Lokal',
            ])->required(),
            Select::make('major_id')->relationship('major', 'name')->searchable()->preload(),
            TextInput::make('hours_per_week')->numeric()->integer()->minValue(1)->maxValue(40)->default(4)->required(),
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
                TextColumn::make('type')->badge(),
                TextColumn::make('major.name')->label('Jurusan')->searchable(),
                TextColumn::make('hours_per_week')->label('Jam/Minggu')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    'core' => 'Umum', 'specialization' => 'Kejuruan', 'local' => 'Muatan Lokal',
                ]),
                SelectFilter::make('major')->relationship('major', 'name')->searchable()->preload(),
                TernaryFilter::make('is_active'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user?->isGuru()
            ? $query->whereHas('teacherAssignments', fn (Builder $assignmentQuery) => $assignmentQuery->where('user_id', $user->id))
            : $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
