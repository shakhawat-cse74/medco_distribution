<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$widgets = DB::table('widgets')->get();
foreach ($widgets as $widget) {
    echo $widget->name . " -> " . json_encode($widget) . "\n";
}
