<?php

namespace Database\Seeders;

use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\Party;
use Illuminate\Database\Seeder;

class ChallanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parties = Party::all();

        if ($parties->isEmpty()) {
            $this->command->warn('No parties found. Run PartySeeder first.');
            return;
        }

        $items = [
            ['description' => 'Steel Bars 10mm', 'unit' => 'kg', 'rate' => 75.00],
            ['description' => 'Cement Bags (50kg)', 'unit' => 'pcs', 'rate' => 350.00],
            ['description' => 'Copper Wire 2.5mm', 'unit' => 'm', 'rate' => 45.00],
            ['description' => 'PVC Pipes 4 inch', 'unit' => 'pcs', 'rate' => 180.00],
            ['description' => 'Paint Bucket 20L', 'unit' => 'pcs', 'rate' => 1200.00],
            ['description' => 'Electrical Switch Board', 'unit' => 'pcs', 'rate' => 450.00],
            ['description' => 'Tiles 2x2', 'unit' => 'sqft', 'rate' => 55.00],
            ['description' => 'Sand', 'unit' => 'ton', 'rate' => 2500.00],
            ['description' => 'Gravel', 'unit' => 'ton', 'rate' => 1800.00],
            ['description' => 'Aluminum Frames', 'unit' => 'set', 'rate' => 3500.00],
        ];

        $challanCount = 0;

        foreach ($parties->take(3) as $party) {
            // Create 2-3 challans per party
            $numChallans = rand(2, 3);
            
            for ($c = 0; $c < $numChallans; $c++) {
                $challanNumber = Challan::generateChallanNumber();
                $challanDate = now()->subDays(rand(1, 30));

                $challan = Challan::create([
                    'party_id' => $party->id,
                    'challan_number' => $challanNumber,
                    'challan_date' => $challanDate,
                    'subtotal' => 0,
                    'is_invoiced' => false,
                ]);

                // Add 2-5 items per challan
                $numItems = rand(2, 5);
                $selectedItems = collect($items)->random($numItems);

                foreach ($selectedItems as $item) {
                    $quantity = rand(5, 100);
                    
                    ChallanItem::create([
                        'challan_id' => $challan->id,
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit' => $item['unit'],
                        'rate' => $item['rate'],
                        'amount' => $quantity * $item['rate'],
                    ]);
                }

                // Recalculate subtotal
                $challan->calculateSubtotal();
                $challanCount++;
            }
        }

        $this->command->info("Created {$challanCount} sample challans with items.");
    }
}
