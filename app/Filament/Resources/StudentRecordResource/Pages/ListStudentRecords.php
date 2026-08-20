<?php

namespace App\Filament\Resources\StudentRecordResource\Pages;

use App\Filament\Resources\StudentRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentRecords extends ListRecords
{
    protected static string $resource = StudentRecordResource::class;

    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
