<?php

namespace Database\Seeders;

use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\Party;
use Illuminate\Database\Seeder;

class PartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parties = [
            [
                'name' => 'ABC Manufacturing Ltd.',
                'address' => '123 Industrial Area, Sector 5, New Delhi - 110001',
                'contact_number' => '9876543210',
                'gst_number' => '07AAACA1234A1ZX',
            ],
            [
                'name' => 'XYZ Trading Co.',
                'address' => '456 Market Street, Andheri East, Mumbai - 400069',
                'contact_number' => '9123456789',
                'gst_number' => '27AABCX1234B1ZY',
            ],
            [
                'name' => 'Global Enterprises',
                'address' => '789 Business Park, Whitefield, Bangalore - 560066',
                'contact_number' => '9988776655',
                'gst_number' => '29AABCE1234C1ZA',
            ],
            [
                'name' => 'Sunrise Industries',
                'address' => '321 Sunset Road, Salt Lake, Kolkata - 700091',
                'contact_number' => '8899001122',
                'gst_number' => null, // No GST
            ],
            [
                'name' => 'Tech Solutions Pvt. Ltd.',
                'address' => '555 IT Corridor, HITEC City, Hyderabad - 500081',
                'contact_number' => '7766554433',
                'gst_number' => '36AABCT1234D1ZB',
            ],
        ];

        foreach ($parties as $partyData) {
            Party::create($partyData);
        }

        $this->command->info('Created 5 sample parties.');
    }
}
