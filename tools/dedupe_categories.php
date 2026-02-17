<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ids = \App\Models\Category::selectRaw('MIN(id) as id')->groupBy('name')->pluck('id');
\App\Models\Category::whereNotIn('id', $ids)->delete();
echo "done";
