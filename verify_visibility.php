<?php

use App\Models\Challan;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\Party;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Helper to login as first user
$user = \App\Models\User::first();
auth()->login($user);

// Create dummy company and party
$company = Company::first();
if (!$company) {
    $company = Company::create(['name' => 'Test Company']);
}

$party = Party::create(['company_id' => $company->id, 'name' => 'Test Party']);

// 1. Create Challan
$challan = Challan::create([
    'company_id' => $company->id,
    'party_id' => $party->id,
    'challan_number' => 'CH-TEST-' . time(),
    'challan_date' => now(),
    'subtotal' => 100,
    'is_invoiced' => false,
]);

echo "Created Challan: {$challan->id}\n";

// Check visibility (should be visible)
$controller = new \App\Http\Controllers\ChallanController();
$response = $controller->getByParty($party);
$data = $response->getData(true);
$visible = false;
foreach ($data['challans'] as $c) {
    if ($c['id'] == $challan->id) $visible = true;
}
echo "Visibility BEFORE invoice: " . ($visible ? 'VISIBLE' : 'HIDDEN') . "\n";

// 2. Invoice it
$invoice = Invoice::create([
    'company_id' => $company->id,
    'party_id' => $party->id,
    'invoice_number' => 'INV-TEST-' . time(),
    'invoice_date' => now(),
    'subtotal' => 100,
    'final_amount' => 100,
]);
$invoice->challans()->attach($challan->id);
$challan->update(['is_invoiced' => true]);

echo "Invoiced Challan. Invoice ID: {$invoice->id}\n";

// Check visibility (should be HIDDEN)
$response = $controller->getByParty($party);
$data = $response->getData(true);
$visible = false;
foreach ($data['challans'] as $c) {
    if ($c['id'] == $challan->id) $visible = true;
}
echo "Visibility AFTER invoice: " . ($visible ? 'VISIBLE' : 'HIDDEN') . "\n";

// 3. Update Challan after 2 seconds
sleep(2);
$challan->update(['subtotal' => 200]); // Simulate change
echo "Updated Challan (subtotal 200)\n";

// Check visibility (should be VISIBLE)
$response = $controller->getByParty($party);
$data = $response->getData(true);
$visible = false;
foreach ($data['challans'] as $c) {
    if ($c['id'] == $challan->id) $visible = true;
}
echo "Visibility AFTER update: " . ($visible ? 'VISIBLE' : 'HIDDEN') . "\n";

// Clean up
$invoice->challans()->detach();
$invoice->delete();
$challan->delete();
$party->delete();
