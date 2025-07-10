<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Wrestler;
use App\Models\WrestlerName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WrestlerSeeder extends Seeder
{
    public function run(): void
    {

        $aew = Promotion::where('abbreviation', 'AEW')->first();
        $wwe = Promotion::where('abbreviation', 'WWE')->first();
        $njpw = Promotion::where('abbreviation', 'NJPW')->first();
        $nxt = Promotion::where('slug', 'nxt')->first();

        //        // Create promotions
        //        $aew = Promotion::create([
        //            'name' => 'All Elite Wrestling',
        //            'abbreviation' => 'AEW',
        //            'country' => 'USA',
        //        ]);
        //
        //        $wwe = Promotion::create([
        //            'name' => 'World Wrestling Entertainment',
        //            'abbreviation' => 'WWE',
        //            'country' => 'USA',
        //        ]);
        //
        //        $njpw = Promotion::create([
        //            'name' => 'New Japan Pro Wrestling',
        //            'abbreviation' => 'NJPW',
        //            'country' => 'Japan',
        //        ]);

        // Create wrestlers (without ring_name because slug depends on primary alias)
        $wrestler1 = Wrestler::create([
            'real_name' => 'Tyson Smith',
            'debut_date' => '2000-02-01',
            'country' => 'Canada',
        ]);

        $wrestler2 = Wrestler::create([
            'real_name' => 'Leati Joseph Anoaʻi',
            'debut_date' => '2010-08-01',
            'country' => 'USA',
        ]);

        $wrestler3 = Wrestler::create([
            'real_name' => 'Kazuchika Okada',
            'debut_date' => '2004-08-29',
            'country' => 'Japan',
        ]);

        // Add wrestler names (aliases)
        $wrestler1->names()->createMany([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Kenny Omega',
                'is_primary' => true,
                'started_at' => '2000-02-01',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Scott Carpenter',
                'is_primary' => false,
                'started_at' => '2001-01-01',
                'ended_at' => '2002-01-01',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'The Cleaner',
                'is_primary' => false,
                'started_at' => '2008-01-01',
                'ended_at' => '2016-01-01',
            ],
        ]);

        $wrestler2->names()->createMany([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Roman Reigns',
                'is_primary' => true,
                'started_at' => '2010-08-01',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Leakee',
                'is_primary' => false,
                'started_at' => '2010-01-01',
                'ended_at' => '2012-12-31',
            ],
        ]);

        $wrestler3->names()->createMany([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Kazuchika Okada',
                'is_primary' => true,
                'started_at' => '2004-08-29',
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Rainmaker',
                'is_primary' => false,
                'started_at' => '2012-01-01',
            ],
        ]);

        // Generate slugs for wrestlers based on primary alias
        foreach ([$wrestler1, $wrestler2, $wrestler3] as $wrestler) {
            $primaryName = $wrestler->primaryName()->first();
            if ($primaryName) {
                $slugBase = Str::slug($primaryName->name);
                $slug = Wrestler::generateUniqueSlug($slugBase);
                $wrestler->slug = $slug;
                $wrestler->saveQuietly();
            }
        }

        // Attach career promotions (all-time)
        $wrestler1->promotions()->attach($aew);
        $wrestler2->promotions()->attach($wwe);
        $wrestler3->promotions()->attach($njpw);

        // Attach active promotions
        $wrestler1->activePromotions()->attach($aew);
        $wrestler2->activePromotions()->attach($wwe);
        $wrestler3->activePromotions()->attach($njpw);

        $wrestlers = [
            [
                'ring_name' => 'Seth Rollins',
                'real_name' => 'Colby Daniel Lopez',
                'country' => 'United States',
                'debut_date' => '2005-01-01',
            ],
            [
                'ring_name' => 'Big E Langston',
                'real_name' => 'Ettore Ewen',
                'country' => 'United States',
                'debut_date' => '2009-12-17',
            ],
            [
                'ring_name' => 'Bo Dallas',
                'real_name' => 'Taylor Michael Rotunda',
                'country' => 'United States',
                'debut_date' => '2008-11-15',
            ],
            [
                'ring_name' => 'Adrian Neville',
                'real_name' => 'Benjamin Satterley',
                'country' => 'England',
                'debut_date' => '2004-03-27',
            ],
            [
                'ring_name' => 'Sami Zayn',
                'real_name' => 'Rami Sebei',
                'country' => 'Canada',
                'debut_date' => '2002-03-01',
            ],
            [
                'ring_name' => 'Kevin Owens',
                'real_name' => 'Kevin Yanick Steen',
                'country' => 'Canada',
                'debut_date' => '2000-01-01',
            ],
            [
                'ring_name' => 'Finn Bálor',
                'real_name' => 'Fergal Devitt',
                'country' => 'Ireland',
                'debut_date' => '2000-06-01',
            ],
            [
                'ring_name' => 'Samoa Joe',
                'real_name' => 'Nuufolau Joel Seanoa',
                'country' => 'United States',
                'debut_date' => '1999-12-01',
            ],
            [
                'ring_name' => 'Shinsuke Nakamura',
                'real_name' => 'Shinsuke Nakamura',
                'country' => 'Japan',
                'debut_date' => '2002-08-29',
            ],
            [
                'ring_name' => 'Bobby Roode',
                'real_name' => 'Robert Francis Roode Jr.',
                'country' => 'Canada',
                'debut_date' => '1998-01-01',
            ],
            [
                'ring_name' => 'Drew McIntyre',
                'real_name' => 'Andrew McLean Galloway IV',
                'country' => 'Scotland',
                'debut_date' => '2001-01-01',
            ],
            [
                'ring_name' => 'Andrade "Cien" Almas',
                'real_name' => 'Manuel Alfonso Andrade Oropeza',
                'country' => 'Mexico',
                'debut_date' => '2003-10-01',
            ],
            [
                'ring_name' => 'Aleister Black',
                'real_name' => 'Tom Büdgen',
                'country' => 'Netherlands',
                'debut_date' => '2008-01-01',
            ],
            [
                'ring_name' => 'Tommaso Ciampa',
                'real_name' => 'Tommaso Whitney',
                'country' => 'United States',
                'debut_date' => '2005-01-01',
            ],
            [
                'ring_name' => 'Johnny Gargano',
                'real_name' => 'John Anthony Nicholas Gargano',
                'country' => 'United States',
                'debut_date' => '2005-01-01',
            ],
            [
                'ring_name' => 'Adam Cole',
                'real_name' => 'Austin Jenkins',
                'country' => 'United States',
                'debut_date' => '2008-06-21',
            ],
            [
                'ring_name' => 'Keith Lee',
                'real_name' => 'Keith Lee',
                'country' => 'United States',
                'debut_date' => '2005-01-01',
            ],
            [
                'ring_name' => 'Karrion Kross',
                'real_name' => 'Kevin Kesar',
                'country' => 'United States',
                'debut_date' => '2014-01-01',
            ],
            [
                'ring_name' => 'Bron Breakker',
                'real_name' => 'Bronson Rechsteiner',
                'country' => 'United States',
                'debut_date' => '2021-07-20',
            ],
            [
                'ring_name' => 'Dolph Ziggler',
                'real_name' => 'Nicholas Theodore Nemeth',
                'country' => 'United States',
                'debut_date' => '2004-01-01',
            ],
            [
                'ring_name' => 'Carmelo Hayes',
                'real_name' => 'Carmelo Hayes',
                'country' => 'United States',
                'debut_date' => '2019-01-01',
            ],
            [
                'ring_name' => 'Ilja Dragunov',
                'real_name' => 'Ilja Rukober',
                'country' => 'Russia',
                'debut_date' => '2007-01-01',
            ],
            [
                'ring_name' => 'Trick Williams',
                'real_name' => 'Matrick Williams',
                'country' => 'United States',
                'debut_date' => '2020-01-01',
            ],
            [
                'ring_name' => 'Ethan Page',
                'real_name' => 'Julian Micevski',
                'country' => 'Canada',
                'debut_date' => '2008-01-01',
            ],
            [
                'ring_name' => 'Oba Femi',
                'real_name' => 'Lawal Kassim',
                'country' => 'Nigeria',
                'debut_date' => '2022-01-01',
            ],
        ];
        foreach ($wrestlers as $data) {
            $wrestler = Wrestler::firstOrCreate(
                ['real_name' => $data['real_name']],
                [
                    'id' => Str::uuid(),
                    'real_name' => $data['real_name'],
                    'country' => $data['country'],
                    'debut_date' => $data['debut_date'],
                    'created_by' => null,
                    'updated_by' => null,
                ]
            );

            // Add primary ring name if not already exists
            WrestlerName::firstOrCreate(
                [
                    'wrestler_id' => $wrestler->id,
                    'name' => $data['ring_name'],
                ],
                [
                    'id' => Str::uuid(),
                    'is_primary' => true,
                    'started_at' => $data['debut_date'],
                    'created_by' => null,
                    'updated_by' => null,
                ]
            );

            // Refresh relationship so we can safely access primary name
            $wrestler->load('primaryName');

            $primaryName = $wrestler->primaryName;

            if ($primaryName && (empty($wrestler->slug) || str_contains($wrestler->slug, 'real-name-'))) {
                $slugBase = Str::slug($primaryName->name);
                $slug = Wrestler::generateUniqueSlug($slugBase);
                $wrestler->slug = $slug;
                $wrestler->saveQuietly();
            }

            // Attach to NXT promotion
            $wrestler->promotions()->syncWithoutDetaching([$nxt->id]);
            $wrestler->activePromotions()->syncWithoutDetaching([$nxt->id]);
        }

    }
}
