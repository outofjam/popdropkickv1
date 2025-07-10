<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\TitleReign;
use App\Models\Wrestler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TitleReignSeeder extends Seeder
{
    public function run(): void
    {

        $championship = Championship::where('slug', 'nxt-championship')->firstOrFail();

        $reigns = [
            // Current Champion - Oba Femi
            [
                'wrestler_slug' => 'oba-femi',
                'won_on' => '2025-01-07',
                'won_at' => 'NXT: New Year\'s Evil 2025',
                'lost_on' => null,
                'lost_at' => null,
            ],

            // Trick Williams (2nd reign)
            [
                'wrestler_slug' => 'trick-williams',
                'won_on' => '2024-10-01',
                'won_at' => 'NXT on The CW (premiere)',
                'lost_on' => '2025-01-07',
                'lost_at' => 'NXT: New Year\'s Evil 2025',
            ],

            // Ethan Page
            [
                'wrestler_slug' => 'ethan-page',
                'won_on' => '2024-07-07',
                'won_at' => 'NXT Heatwave 2024',
                'lost_on' => '2024-10-01',
                'lost_at' => 'NXT on The CW (premiere)',
            ],

            // Trick Williams (1st reign)
            [
                'wrestler_slug' => 'trick-williams',
                'won_on' => '2024-04-23',
                'won_at' => 'NXT Spring Breakin\' 2024 (Night 1)',
                'lost_on' => '2024-07-07',
                'lost_at' => 'NXT Heatwave 2024',
            ],

            // Ilja Dragunov
            [
                'wrestler_slug' => 'ilja-dragunov',
                'won_on' => '2023-09-30',
                'won_at' => 'NXT No Mercy 2023',
                'lost_on' => '2024-04-23',
                'lost_at' => 'NXT Spring Breakin\' 2024 (Night 1)',
            ],

            // Carmelo Hayes
            [
                'wrestler_slug' => 'carmelo-hayes',
                'won_on' => '2023-04-01',
                'won_at' => 'NXT Stand & Deliver 2023',
                'lost_on' => '2023-09-30',
                'lost_at' => 'NXT No Mercy 2023',
            ],

            // Bron Breakker (2nd reign)
            [
                'wrestler_slug' => 'bron-breakker',
                'won_on' => '2022-09-04',
                'won_at' => 'NXT Worlds Collide 2022',
                'lost_on' => '2023-04-01',
                'lost_at' => 'NXT Stand & Deliver 2023',
            ],

            // Dolph Ziggler
            [
                'wrestler_slug' => 'dolph-ziggler',
                'won_on' => '2022-04-02',
                'won_at' => 'NXT Stand & Deliver 2022',
                'lost_on' => '2022-04-05',
                'lost_at' => 'Monday Night Raw',
            ],

            // Bron Breakker (1st reign)
            [
                'wrestler_slug' => 'bron-breakker',
                'won_on' => '2022-01-04',
                'won_at' => 'NXT New Year\'s Evil 2022',
                'lost_on' => '2022-04-02',
                'lost_at' => 'NXT Stand & Deliver 2022',
            ],

            // Tommaso Ciampa (2nd reign)
            [
                'wrestler_slug' => 'tommaso-ciampa',
                'won_on' => '2021-09-14',
                'won_at' => 'NXT 2.0 (premiere)',
                'lost_on' => '2022-01-04',
                'lost_at' => 'NXT New Year\'s Evil 2022',
            ],

            // Samoa Joe (3rd reign)
            [
                'wrestler_slug' => 'samoa-joe',
                'won_on' => '2021-08-22',
                'won_at' => 'NXT TakeOver 36',
                'lost_on' => '2021-09-12',
                'lost_at' => null, // Relinquished due to injury/COVID
            ],

            // Karrion Kross (2nd reign)
            [
                'wrestler_slug' => 'karrion-kross',
                'won_on' => '2021-04-08',
                'won_at' => 'NXT TakeOver: Stand & Deliver (Night 2)',
                'lost_on' => '2021-08-22',
                'lost_at' => 'NXT TakeOver 36',
            ],

            // Finn Bálor (2nd reign)
            [
                'wrestler_slug' => 'finn-balor',
                'won_on' => '2020-09-08',
                'won_at' => 'NXT Super Tuesday II',
                'lost_on' => '2021-04-08',
                'lost_at' => 'NXT TakeOver: Stand & Deliver (Night 2)',
            ],

            // Karrion Kross (1st reign)
            [
                'wrestler_slug' => 'karrion-kross',
                'won_on' => '2020-08-22',
                'won_at' => 'NXT TakeOver: XXX',
                'lost_on' => '2020-08-26',
                'lost_at' => null, // Relinquished due to injury
            ],

            // Keith Lee
            [
                'wrestler_slug' => 'keith-lee',
                'won_on' => '2020-07-08',
                'won_at' => 'NXT Great American Bash (Night 2)',
                'lost_on' => '2020-08-22',
                'lost_at' => 'NXT TakeOver: XXX',
            ],

            // Adam Cole
            [
                'wrestler_slug' => 'adam-cole',
                'won_on' => '2019-06-01',
                'won_at' => 'NXT TakeOver: XXV',
                'lost_on' => '2020-07-08',
                'lost_at' => 'NXT Great American Bash (Night 2)',
            ],

            // Johnny Gargano
            [
                'wrestler_slug' => 'johnny-gargano',
                'won_on' => '2019-04-05',
                'won_at' => 'NXT TakeOver: New York',
                'lost_on' => '2019-06-01',
                'lost_at' => 'NXT TakeOver: XXV',
            ],

            // Tommaso Ciampa (1st reign) - Vacated due to injury
            [
                'wrestler_slug' => 'tommaso-ciampa',
                'won_on' => '2018-07-25',
                'won_at' => 'NXT (TV)',
                'lost_on' => '2019-02-20',
                'lost_at' => null, // Vacated due to injury
            ],

            // Aleister Black
            [
                'wrestler_slug' => 'aleister-black',
                'won_on' => '2018-04-07',
                'won_at' => 'NXT TakeOver: New Orleans',
                'lost_on' => '2018-07-25',
                'lost_at' => 'NXT (TV)',
            ],

            // Andrade "Cien" Almas
            [
                'wrestler_slug' => 'andrade-cien-almas',
                'won_on' => '2017-11-18',
                'won_at' => 'NXT TakeOver: WarGames',
                'lost_on' => '2018-04-07',
                'lost_at' => 'NXT TakeOver: New Orleans',
            ],

            // Drew McIntyre
            [
                'wrestler_slug' => 'drew-mcintyre',
                'won_on' => '2017-08-19',
                'won_at' => 'NXT TakeOver: Brooklyn III',
                'lost_on' => '2017-11-18',
                'lost_at' => 'NXT TakeOver: WarGames',
            ],

            // Bobby Roode
            [
                'wrestler_slug' => 'bobby-roode',
                'won_on' => '2017-01-28',
                'won_at' => 'NXT TakeOver: San Antonio',
                'lost_on' => '2017-08-19',
                'lost_at' => 'NXT TakeOver: Brooklyn III',
            ],

            // Shinsuke Nakamura (2nd reign)
            [
                'wrestler_slug' => 'shinsuke-nakamura',
                'won_on' => '2016-12-03',
                'won_at' => 'NXT Live Event (Osaka, Japan)',
                'lost_on' => '2017-01-28',
                'lost_at' => 'NXT TakeOver: San Antonio',
            ],

            // Samoa Joe (2nd reign)
            [
                'wrestler_slug' => 'samoa-joe',
                'won_on' => '2016-11-19',
                'won_at' => 'NXT TakeOver: Toronto',
                'lost_on' => '2016-12-03',
                'lost_at' => 'NXT Live Event (Osaka, Japan)',
            ],

            // Shinsuke Nakamura (1st reign)
            [
                'wrestler_slug' => 'shinsuke-nakamura',
                'won_on' => '2016-08-20',
                'won_at' => 'NXT TakeOver: Brooklyn II',
                'lost_on' => '2016-11-19',
                'lost_at' => 'NXT TakeOver: Toronto',
            ],

            // Samoa Joe (1st reign)
            [
                'wrestler_slug' => 'samoa-joe',
                'won_on' => '2016-04-21',
                'won_at' => 'NXT Live Event (Lowell, Massachusetts)',
                'lost_on' => '2016-08-20',
                'lost_at' => 'NXT TakeOver: Brooklyn II',
            ],

            // Finn Bálor (1st reign)
            [
                'wrestler_slug' => 'finn-balor',
                'won_on' => '2015-07-04',
                'won_at' => 'WWE Beast in the East',
                'lost_on' => '2016-04-21',
                'lost_at' => 'NXT Live Event (Lowell, Massachusetts)',
            ],

            // Kevin Owens
            [
                'wrestler_slug' => 'kevin-owens',
                'won_on' => '2015-02-11',
                'won_at' => 'NXT TakeOver: Rival',
                'lost_on' => '2015-07-04',
                'lost_at' => 'WWE Beast in the East',
            ],

            // Sami Zayn
            [
                'wrestler_slug' => 'sami-zayn',
                'won_on' => '2014-12-11',
                'won_at' => 'NXT TakeOver: R Evolution',
                'lost_on' => '2015-02-11',
                'lost_at' => 'NXT TakeOver: Rival',
            ],

            // Adrian Neville (PAC)
            [
                'wrestler_slug' => 'adrian-neville',
                'won_on' => '2014-02-27',
                'won_at' => 'NXT ArRIVAL',
                'lost_on' => '2014-12-11',
                'lost_at' => 'NXT TakeOver: R Evolution',
            ],

            // Bo Dallas
            [
                'wrestler_slug' => 'bo-dallas',
                'won_on' => '2013-06-20',
                'won_at' => 'NXT Live Event (Jacksonville, Florida)',
                'lost_on' => '2014-02-27',
                'lost_at' => 'NXT ArRIVAL',
            ],

            // Big E
            [
                'wrestler_slug' => 'big-e-langston',
                'won_on' => '2013-01-09',
                'won_at' => 'NXT (TV)',
                'lost_on' => '2013-06-20',
                'lost_at' => 'NXT Live Event (Jacksonville, Florida)',
            ],

            // Seth Rollins (Inaugural Champion)
            [
                'wrestler_slug' => 'seth-rollins',
                'won_on' => '2012-07-26',
                'won_at' => 'NXT Gold Rush Tournament Finals',
                'lost_on' => '2013-01-09',
                'lost_at' => 'NXT (TV)',
            ],
        ];

        foreach ($reigns as $data) {
            $wrestler = Wrestler::where('slug', $data['wrestler_slug'])->first();

            if (! $wrestler) {
                echo "❌ Wrestler not found: {$data['wrestler_slug']}\n";

                continue;
            }

            TitleReign::create([
                'id' => Str::uuid(),
                'championship_id' => $championship->id,
                'wrestler_id' => $wrestler->id,
                'won_on' => $data['won_on'],
                'reign_number' => 1,
                'won_at' => $data['won_at'],
                'lost_on' => $data['lost_on'],
                'lost_at' => $data['lost_at'],
                'created_by' => null,
                'updated_by' => null,
            ]);
        }
    }

    /**
     * Create a title reign record.
     *
     * @param  string  $wonOn  Date won (Y-m-d)
     * @param  string|null  $wonAt  Venue or event where won
     * @param  string|null  $lostOn  Date lost (Y-m-d) or null if current champ
     * @param  string|null  $lostAt  Venue or event where lost or null
     * @param  string  $winType  Win type (pinfall, submission, etc.)
     */
    private function createReign(
        string $championshipId,
        string $wrestlerId,
        string $wonOn,
        ?string $wonAt,
        ?string $lostOn,
        ?string $lostAt,
        string $winType
    ): void {
        $lastReignNumber = TitleReign::where('championship_id', $championshipId)
            ->where('wrestler_id', $wrestlerId)
            ->max('reign_number');

        $nextReignNumber = $lastReignNumber ? $lastReignNumber + 1 : 1;

        TitleReign::create([
            'id' => (string) Str::uuid(),
            'championship_id' => $championshipId,
            'wrestler_id' => $wrestlerId,
            'won_on' => $wonOn,
            'won_at' => $wonAt,
            'lost_on' => $lostOn,
            'lost_at' => $lostAt,
            'win_type' => $winType,
            'reign_number' => $nextReignNumber,
        ]);

    }
}
