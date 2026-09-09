<?php

namespace Database\Seeders;

use App\Models\PropertyCategory;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyCatalogSeeder extends Seeder
{
    public function run(): void
    {
        PropertyCategory::query()->delete();
        PropertyType::query()->delete();

        $types = [
            [
                'name' => 'Room',
                'slug' => 'room',
                'categories' => ['Single Room', 'Shared Room', 'Room Set'],
            ],
            [
                'name' => 'PG',
                'slug' => 'pg',
                'categories' => ['Boys PG', 'Girls PG', 'Co-Living'],
            ],
            [
                'name' => 'Flat',
                'slug' => 'flat',
                'categories' => ['1RK', '1BHK', '2BHK', '3BHK', '4BHK'],
            ],
            [
                'name' => 'House',
                'slug' => 'house',
                'categories' => ['Independent House', 'Villa', 'Duplex'],
            ],
            [
                'name' => 'Shop',
                'slug' => 'shop',
                'categories' => ['Road Facing', 'Inside Market', 'Corner Shop', 'Food Shop', 'Main Street'],
            ],
            [
                'name' => 'Office',
                'slug' => 'office',
                'categories' => ['Private Office', 'Coworking', 'Furnished Office', 'Unfurnished Office'],
            ],
            [
                'name' => 'Showroom',
                'slug' => 'showroom',
                'categories' => ['Retail Showroom', 'Commercial Showroom'],
            ],
            [
                'name' => 'Warehouse',
                'slug' => 'warehouse',
                'categories' => ['Warehouse', 'Godown', 'Industrial Shed'],
            ],
            [
                'name' => 'Plot / Land',
                'slug' => 'plot-land',
                'categories' => ['Residential Plot', 'Commercial Land', 'Agricultural / Farm Land', 'Industrial Plot'],
            ],
        ];

        foreach ($types as $typeData) {
            $propertyType = PropertyType::create([
                'name' => $typeData['name'],
                'slug' => $typeData['slug'],
                'status' => true,
            ]);

            foreach ($typeData['categories'] as $categoryName) {
                PropertyCategory::create([
                    'property_type_id' => $propertyType->id,
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'status' => true,
                ]);
            }
        }
    }
}
