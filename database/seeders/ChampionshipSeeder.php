<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChampionshipSeeder extends Seeder
{
    public function run(): void
    {
        $aew = Promotion::where('abbreviation', 'AEW')->first();
        $wwe = Promotion::where('abbreviation', 'WWE')->first();
        $njpw = Promotion::where('abbreviation', 'NJPW')->first();

        Championship::create([
            'id' => (string) Str::uuid(),
            'name' => 'AEW World Championship',
            'promotion_id' => $aew->id,
            'introduced_at' => '2019-05-25',
            'weight_class' => 'Heavyweight',
            'active' => true,
        ]);

        Championship::create([
            'id' => (string) Str::uuid(),
            'name' => 'WWE Universal Championship',
            'promotion_id' => $wwe->id,
            'introduced_at' => '2016-08-21',
            'weight_class' => 'Heavyweight',
            'active' => true,
        ]);

        Championship::create([
            'id' => (string) Str::uuid(),
            'name' => 'IWGP World Heavyweight Championship',
            'promotion_id' => $njpw->id,
            'introduced_at' => '1987-06-12',
            'weight_class' => 'Heavyweight',
            'active' => true,
        ]);

        $nxt = Promotion::where('slug', 'nxt')->first();

        $championships = [
            // AEW Titles
            [
                'id' => Str::uuid(),
                'promotion_id' => $aew->id,
                'name' => 'AEW World Championship',
                'slug' => 'aew-world-championship',
                'abbreviation' => 'AEW World',
                'division' => 'Heavyweight',
                'introduced_at' => '2019-05-25',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $aew->id,
                'name' => 'AEW TNT Championship',
                'slug' => 'aew-tnt-championship',
                'abbreviation' => 'AEW TNT',
                'division' => 'Mid-Heavyweight',
                'introduced_at' => '2020-03-18',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $aew->id,
                'name' => "AEW Women's World Championship",
                'slug' => 'aew-womens-world-championship',
                'abbreviation' => 'AEW Women',
                'division' => 'Women',
                'introduced_at' => '2019-10-02',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $aew->id,
                'name' => 'AEW World Tag Team Championship',
                'slug' => 'aew-world-tag-team-championship',
                'abbreviation' => 'AEW Tag',
                'division' => 'Tag Team',
                'introduced_at' => '2019-10-30',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],

            // NXT Titles
            [
                'id' => Str::uuid(),
                'promotion_id' => $nxt->id,
                'name' => 'NXT Championship',
                'slug' => 'nxt-championship',
                'abbreviation' => 'NXT',
                'division' => 'Heavyweight',
                'introduced_at' => '2012-07-01',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $nxt->id,
                'name' => 'NXT North American Championship',
                'slug' => 'nxt-north-american-championship',
                'abbreviation' => 'NXT NA',
                'division' => 'Mid-Heavyweight',
                'introduced_at' => '2018-03-07',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $nxt->id,
                'name' => "NXT Women's Championship",
                'slug' => 'nxt-womens-championship',
                'abbreviation' => 'NXT Women',
                'division' => 'Women',
                'introduced_at' => '2013-04-05',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
            [
                'id' => Str::uuid(),
                'promotion_id' => $nxt->id,
                'name' => 'NXT Tag Team Championship',
                'slug' => 'nxt-tag-team-championship',
                'abbreviation' => 'NXT Tag',
                'division' => 'Tag Team',
                'introduced_at' => '2013-01-23',
                'weight_class' => null,
                'active' => true,
                'created_by' => null,
                'updated_by' => null,
            ],
        ];

        foreach ($championships as $champ) {
            Championship::updateOrCreate(
                ['slug' => $champ['slug']],
                $champ
            );
        }

    }
}
