<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\PurchaseController;

$user = User::first();
auth()->login($user);

$req = Request::create('/purchases/purchase-data', 'POST', [
    'starting_date' => '2025-01-01',
    'ending_date' => '2026-12-31',
    'warehouse_id' => 0,
    'purchase_status' => 0,
    'payment_status' => 0,
    'length' => 10,
    'start' => 0,
    'draw' => 1,
    'order' => [
        ['column' => 1, 'dir' => 'desc']
    ]
]);

ob_start();
app(PurchaseController::class)->purchaseData($req);
$out = ob_get_clean();

$data = json_decode($out, true);
echo "JSON Parse Status: " . (json_last_error() === JSON_ERROR_NONE ? "VALID JSON" : "INVALID JSON: " . json_last_error_msg()) . "\n";
echo "Total Records: " . count($data['data'] ?? []) . "\n";
foreach ($data['data'] ?? [] as $r) {
    echo "Ref: {$r['reference_no']} | Date: {$r['date']} | Products: {$r['products']} | Currency: {$r['currency']}\n";
}
