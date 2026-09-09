<?php

namespace Database\Seeders;

use App\Models\RoomOption;
use Illuminate\Database\Seeder;

class RoomOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // Furnishing Types
            ['group' => 'furnishing_type', 'key' => 'fully_furnished', 'label' => 'Fully Furnished', 'sort_order' => 1],
            ['group' => 'furnishing_type', 'key' => 'semi_furnished', 'label' => 'Semi Furnished', 'sort_order' => 2],
            ['group' => 'furnishing_type', 'key' => 'unfurnished', 'label' => 'Unfurnished', 'sort_order' => 3],

            // Preferred Tenants
            ['group' => 'tenant_type', 'key' => 'anyone', 'label' => 'Anyone', 'sort_order' => 1],
            ['group' => 'tenant_type', 'key' => 'family', 'label' => 'Family', 'sort_order' => 2],
            ['group' => 'tenant_type', 'key' => 'bachelor', 'label' => 'Bachelor', 'sort_order' => 3],
            ['group' => 'tenant_type', 'key' => 'girls', 'label' => 'Girls Only', 'sort_order' => 4],
            ['group' => 'tenant_type', 'key' => 'boys', 'label' => 'Boys Only', 'sort_order' => 5],
            ['group' => 'tenant_type', 'key' => 'company_lease', 'label' => 'Company Lease', 'sort_order' => 6],

            // Amenities
            ['group' => 'amenity', 'key' => 'wifi', 'label' => 'High-speed WiFi', 'sort_order' => 1],
            ['group' => 'amenity', 'key' => 'parking', 'label' => 'Car & Bike Parking', 'sort_order' => 2],
            ['group' => 'amenity', 'key' => 'ac', 'label' => 'Air Conditioner', 'sort_order' => 3],
            ['group' => 'amenity', 'key' => 'power_backup', 'label' => 'Power Backup', 'sort_order' => 4],
            ['group' => 'amenity', 'key' => 'lift', 'label' => 'Lift / Elevator', 'sort_order' => 5],
            ['group' => 'amenity', 'key' => 'security', 'label' => '24x7 Security Guard', 'sort_order' => 6],
            ['group' => 'amenity', 'key' => 'cctv', 'label' => 'CCTV Surveillance', 'sort_order' => 7],
            ['group' => 'amenity', 'key' => 'water_supply', 'label' => '24hr Water Supply', 'sort_order' => 8],
            ['group' => 'amenity', 'key' => 'gym', 'label' => 'Fitness Gym', 'sort_order' => 9],
            ['group' => 'amenity', 'key' => 'swimming_pool', 'label' => 'Swimming Pool', 'sort_order' => 10],
            ['group' => 'amenity', 'key' => 'club_house', 'label' => 'Club House', 'sort_order' => 11],
            ['group' => 'amenity', 'key' => 'park', 'label' => 'Park / Green Area', 'sort_order' => 12],
            ['group' => 'amenity', 'key' => 'fire_safety', 'label' => 'Fire Safety', 'sort_order' => 13],
            ['group' => 'amenity', 'key' => 'gas_pipeline', 'label' => 'Piped Gas (PNG)', 'sort_order' => 14],
        ];

        foreach ($options as $opt) {
            RoomOption::updateOrCreate(
                ['key' => $opt['key']],
                [
                    'group' => $opt['group'],
                    'label' => $opt['label'],
                    'sort_order' => $opt['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
