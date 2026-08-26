<?php

namespace App\Filament\Resources\StorageAuditLogResource\Pages;

use App\Filament\Resources\StorageAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListStorageAuditLogs extends ListRecords
{
    protected static string $resource = StorageAuditLogResource::class;
}
