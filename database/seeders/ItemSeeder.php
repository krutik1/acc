<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ChallanItem;
use App\Models\Item;
use App\Models\Challan;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating items from ChallanItems...');

        // Get all unique item names per company
        // We need to join with challans table to get company_id
        $items = ChallanItem::join('challans', 'challan_items.challan_id', '=', 'challans.id')
            ->select(
                'challans.company_id',
                'challan_items.description as name',
                'challan_items.hsn_code',
                'challan_items.unit',
                'challan_items.rate' // We take the last used rate
            )
            ->whereNotNull('challans.company_id')
            ->orderBy('challan_items.created_at', 'desc') // Get latest first to use latest rate/hsn
            ->get()
            ->unique(function ($item) {
                return $item->company_id . '-' . strtolower(trim($item->name));
            });

        $count = 0;
        foreach ($items as $sourceItem) {
            $exists = Item::where('company_id', $sourceItem->company_id)
                ->where('name', $sourceItem->name)
                ->exists();

            if (!$exists) {
                Item::create([
                    'company_id' => $sourceItem->company_id,
                    'name' => $sourceItem->name,
                    'hsn_code' => $sourceItem->hsn_code,
                    'rate' => $sourceItem->rate,
                    'unit' => $sourceItem->unit,
                    'status' => 'active',
                ]);
                $count++;
            }
        }

        $this->command->info("Migrated {$count} items successfully.");
    }
}
