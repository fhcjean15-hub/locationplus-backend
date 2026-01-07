<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BienSeeder extends Seeder
{
    public function run()
    {
        $ownerId = '019b2295-0e22-72d3-bfb9-f2fb2f7e9421';
        $categories = ['immobilier', 'hotel', 'hebergement', 'meuble', 'vehicule'];
        $cities = ['Cotonou', 'Porto-Novo', 'Parakou', 'Abomey', 'Bohicon'];
        $districts = ['Calavi', 'Akpakpa', 'Adjohoun', 'Godomey', 'Dassa-Zoumé'];

        $biens = [];

        for ($i = 1; $i <= 50; $i++) {
            $category = $categories[array_rand($categories)];
            $transactionType = match($category) {
                'immobilier' => ['vente','location'][array_rand(['vente','location'])],
                'hotel' => 'location',
                'hebergement' => 'location',
                'meuble' => ['vente','location'][array_rand(['vente','location'])],
                'vehicule' => ['vente','location'][array_rand(['vente','location'])],
            };

            $price = match($category) {
                'immobilier' => rand(1500000, 10000000),
                'hotel' => rand(20000, 100000),
                'hebergement' => rand(15000, 50000),
                'meuble' => rand(5000, 50000),
                'vehicule' => rand(500000, 5000000),
            };

            $title = match($category) {
                'immobilier' => 'Appartement à ' . $cities[array_rand($cities)],
                'hotel' => 'Hôtel ' . Str::random(5),
                'hebergement' => 'Chambre ' . Str::random(3),
                'meuble' => 'Meuble ' . Str::random(4),
                'vehicule' => 'Véhicule ' . Str::random(4),
            };

            $images = match($category) {
                'immobilier' => [
                    "http://192.168.100.246:8000/storage/bien_images/f19f07a8c32d2415061e0bdf7861363abf4905488d8da13bcebd99faa0363d5b.jpg", 
                    "http://192.168.100.246:8000/storage/bien_images/2f22fa2da72e12c6d2b533f1e138a53cc45e81b686b9fa5721d5dba6478eb29c.jpg"
                ],
                'hotel' => [
                    "http://192.168.100.246:8000/storage/bien_images/f19f07a8c32d2415061e0bdf7861363abf4905488d8da13bcebd99faa0363d5b.jpg", 
                    "http://192.168.100.246:8000/storage/bien_images/2f22fa2da72e12c6d2b533f1e138a53cc45e81b686b9fa5721d5dba6478eb29c.jpg"
                ],
                'hebergement' => [
                    "http://192.168.100.246:8000/storage/bien_images/2f22fa2da72e12c6d2b533f1e138a53cc45e81b686b9fa5721d5dba6478eb29c.jpg"
                ],
                'meuble' => [
                    "http://192.168.100.246:8000/storage/bien_images/f19f07a8c32d2415061e0bdf7861363abf4905488d8da13bcebd99faa0363d5b.jpg",
                ],
                'vehicule' => [
                    "http://192.168.100.246:8000/storage/bien_images/f19f07a8c32d2415061e0bdf7861363abf4905488d8da13bcebd99faa0363d5b.jpg",
                ],
            };

            $attributes = match($category) {
                'immobilier' => [
                    'surface' => rand(40, 250).' m²',
                    'rooms' => rand(1, 5),
                    'bathrooms' => rand(1, 3),
                    'furnished' => rand(0,1) ? true : false,
                    'parking' => rand(0,1) ? true : false
                ],
                'hotel' => [
                    'room_type' => 'Standard',
                    'capacity' => rand(1, 4),
                    'wifi' => rand(0,1) ? true : false,
                    'air_conditioning' => rand(0,1) ? true : false
                ],
                'hebergement' => [
                    'room_type' => 'Chambre simple',
                    'capacity' => rand(1, 3),
                    'wifi' => rand(0,1) ? true : false,
                    'air_conditioning' => rand(0,1) ? true : false,
                    'bathroom_private' => rand(0,1) ? true : false
                ],
                'meuble' => [
                    'material' => ['Bois','Métal','Plastique'][array_rand(['Bois','Métal','Plastique'])],
                    'dimensions' => rand(50,200).'x'.rand(50,200).' cm',
                    'condition' => ['Neuf','Occasion'][array_rand(['Neuf','Occasion'])]
                ],
                'vehicule' => [
                    'brand' => ['Toyota','Honda','Mercedes','Renault'][array_rand(['Toyota','Honda','Mercedes','Renault'])],
                    'model' => 'Model '.rand(100,999),
                    'year' => rand(2010,2023),
                    'fuel' => ['Essence','Diesel','Electrique'][array_rand(['Essence','Diesel','Electrique'])],
                    'gearbox' => ['Manuelle','Automatique'][array_rand(['Manuelle','Automatique'])],
                    'mileage' => rand(5000,200000)
                ],
            };

            $biens[] = [
                'category' => $category,
                'transaction_type' => $transactionType,
                'title' => $title,
                'description' => 'Description détaillée pour le bien '.$i,
                'price' => $price,
                'city' => $cities[array_rand($cities)],
                'district' => $districts[array_rand($districts)],
                'images' => json_encode($images),
                'attributes' => json_encode($attributes),
                'status' => 'disponible',
                'actif' => true,
                'created_at' => Carbon::now(),
                'user_id' => $ownerId
            ];
        }

        DB::table('biens')->insert($biens);
    }
}
