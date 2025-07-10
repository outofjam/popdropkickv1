<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromotionSeeder extends Seeder
{
    public function run()
    {
        $promotions = [
            [
                'id' => Str::uuid(),
                'name' => 'World Wrestling Entertainment',
                'slug' => 'world-wrestling-entertainment',
                'abbreviation' => 'WWE',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'All Elite Wrestling',
                'slug' => 'all-elite-wrestling',
                'abbreviation' => 'AEW',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'New Japan Pro-Wrestling',
                'slug' => 'new-japan-pro-wrestling',
                'abbreviation' => 'NJPW',
                'country' => 'Japan',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Impact Wrestling',
                'slug' => 'impact-wrestling',
                'abbreviation' => 'Impact',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Ring of Honor',
                'slug' => 'ring-of-honor',
                'abbreviation' => 'ROH',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'NXT',
                'slug' => 'nxt',
                'abbreviation' => 'NXT',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Consejo Mundial de Lucha Libre',
                'slug' => 'consejo-mundial-de-lucha-libre',
                'abbreviation' => 'CMLL',
                'country' => 'Mexico',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Lucha Libre AAA Worldwide',
                'slug' => 'lucha-libre-aaa-worldwide',
                'abbreviation' => 'AAA',
                'country' => 'Mexico',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Major League Wrestling',
                'slug' => 'major-league-wrestling',
                'abbreviation' => 'MLW',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Game Changer Wrestling',
                'slug' => 'game-changer-wrestling',
                'abbreviation' => 'GCW',
                'country' => 'USA',
                'created_by' => null,
                'updated_by' => null,
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::updateOrCreate(
                ['slug' => $promo['slug']],
                $promo
            );
        }
    }
}
