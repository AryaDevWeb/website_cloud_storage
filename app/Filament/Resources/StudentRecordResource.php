<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentRecordResource\Pages;
use App\Models\StudentRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentRecordResource extends Resource
{
    protected static ?string $model = StudentRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $modelLabel = 'Data Siswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Siswa')
                ->relationship('student', 'name', fn (Builder $query) => $query->role('siswa'))
                ->searchable()->preload()->required(),
            Select::make('classroom_id')->relationship('classroom', 'name')->searchable()->preload()->required(),
            TextInput::make('academic_year')->required()->maxLength(255)->placeholder('2026/2027'),
            TextInput::make('student_id')->required()->maxLength(255)->unique(ignoreRecord: true),
            Select::make('status')->options([
                'active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'graduate' => 'Lulus', 'dropout' => 'Berhenti',
            ])->required()->default('active'),
            DatePicker::make('enrollment_date')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')->label('Siswa')->searchable()->sortable(),
                TextColumn::make('student.studentProfile.nisn')->label('NISN')->searchable(),
                TextColumn::make('classroom.name')->label('Kelas')->searchable()->sortable(),
                TextColumn::make('academic_year')->searchable(),
                TextColumn::make('student_id')->label('ID Siswa')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('enrollment_date')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'graduate' => 'Lulus', 'dropout' => 'Berhenti',
                ]),
                SelectFilter::make('classroom')->relationship('classroom', 'name')->searchable()->preload(),
            ])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user?->isSiswa() ? $query->where('user_id', $user->id) : $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentRecords::route('/'),
            'create' => Pages\CreateStudentRecord::route('/create'),
            'edit' => Pages\EditStudentRecord::route('/{record}/edit'),
        ];
    }
}
