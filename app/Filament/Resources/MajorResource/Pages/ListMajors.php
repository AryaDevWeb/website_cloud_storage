<?php

namespace App\Filament\Resources\MajorResource\Pages;

use App\Filament\Resources\MajorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMajors extends ListRecords
{
    protected static string $resource = MajorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
