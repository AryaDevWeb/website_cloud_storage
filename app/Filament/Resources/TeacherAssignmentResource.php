<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Models\TeacherAssignment;
use Filament\Forms\Components\Select;
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

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = TeacherAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Penugasan Guru';
    protected static ?string $modelLabel = 'Penugasan Guru';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Guru')
                ->relationship('teacher', 'name', fn (Builder $query) => $query->role('guru'))
                ->searchable()->preload()->required(),
            Select::make('subject_id')->relationship('subject', 'name')->searchable()->preload()->required(),
            Select::make('classroom_id')->relationship('classroom', 'name')->searchable()->preload()->required(),
            Select::make('academic_year')->options(fn () => [date('Y').'/'.(date('Y') + 1) => date('Y').'/'.(date('Y') + 1)])->required(),
            Select::make('role')->options([
                'main_teacher' => 'Guru Utama', 'assistant_teacher' => 'Guru Pendamping',
            ])->required()->default('main_teacher'),
            Toggle::make('is_homeroom_teacher')->label('Wali Kelas')->default(false),
            Toggle::make('is_active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')->label('Guru')->searchable()->sortable(),
                TextColumn::make('subject.name')->label('Mata Pelajaran')->searchable()->sortable(),
                TextColumn::make('classroom.name')->label('Kelas')->searchable()->sortable(),
                TextColumn::make('academic_year')->searchable(),
                TextColumn::make('role')->badge(),
                IconColumn::make('is_homeroom_teacher')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('role')->options(['main_teacher' => 'Guru Utama', 'assistant_teacher' => 'Guru Pendamping']),
                TernaryFilter::make('is_active'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->defaultSort('academic_year', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user?->isGuru() ? $query->where('user_id', $user->id) : $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAssignments::route('/'),
            'create' => Pages\CreateTeacherAssignment::route('/create'),
            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),
        ];
    }
}
