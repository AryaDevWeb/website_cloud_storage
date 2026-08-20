<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Models\Classroom;
use Filament\Forms\Components\Select;
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

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Kelas';
    protected static ?string $modelLabel = 'Kelas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(255),
            Select::make('major_id')->relationship('major', 'name')->searchable()->preload()->required(),
            Select::make('grade_level')->options([10 => 'X', 11 => 'XI', 12 => 'XII'])->required(),
            TextInput::make('academic_year')->required()->maxLength(255)->placeholder('2026/2027'),
            TextInput::make('capacity')->numeric()->integer()->minValue(1)->maxValue(500)->default(36)->required(),
            TextInput::make('room')->maxLength(255),
            Select::make('homeroom_teacher_id')
                ->relationship('homeroomTeacher', 'name', fn (Builder $query) => $query->role('guru'))
                ->searchable()->preload(),
            Toggle::make('is_active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('major.name')->label('Jurusan')->searchable()->sortable(),
                TextColumn::make('grade_level')->formatStateUsing(fn ($state) => ['10' => 'X', '11' => 'XI', '12' => 'XII'][(string) $state] ?? $state),
                TextColumn::make('academic_year')->searchable(),
                TextColumn::make('homeroomTeacher.name')->label('Wali Kelas')->searchable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
