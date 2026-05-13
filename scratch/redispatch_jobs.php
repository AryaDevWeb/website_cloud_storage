<?php

use App\Models\Gallery;
use App\Jobs\ProcessFilePreview;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ambil semua file yang belum ada thumbnail tapi tipenya seharusnya punya thumbnail
$galleries = Gallery::withTrashed()
    ->whereNull('thumbnail_path')
    ->whereIn('preview_type', ['image', 'video', 'pdf', 'office'])
    ->get();

$count = 0;
foreach ($galleries as $gallery) {
    $gallery->update(['conversion_status' => 'pending']);
    ProcessFilePreview::dispatch($gallery->id);
    $count++;
}

echo "Berhasil memicu ulang proses untuk $count file.\n";
