<?php

use App\Models\Gallery;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = Gallery::whereNull('thumbnail_path')
    ->where('conversion_status', 'done')
    ->whereIn('preview_type', ['image', 'video', 'pdf', 'office'])
    ->update(['conversion_status' => 'pending']);

echo "Reset $count files to pending for re-processing.\n";
