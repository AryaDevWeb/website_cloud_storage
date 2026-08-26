<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class StorageWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Ruang File';

    protected static ?string $title = 'Ruang File';

    protected static ?string $navigationGroup = 'Storage';

    protected static string $view = 'filament.pages.storage-workspace';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isActive();
    }
}
