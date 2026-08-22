<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cat = DB::table('categories')->where('name', 'Disposable')->first();
if($cat) {
    echo "Parent ID: " . $cat->id . "\n";
    $children = DB::table('categories')->where('parent_id', $cat->id)->get();
    echo "Children Count: " . count($children) . "\n";
    foreach($children as $c) {
        echo " - " . $c->name . " (is_active: " . $c->is_active . ")\n";
    }
} else {
    echo "Category not found\n";
}
