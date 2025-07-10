<?php

// database/seeders/WrestlerNameSeeder.php

namespace Database\Seeders;

use App\Models\Wrestler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WrestlerNameSeeder extends Seeder
{
    public function run(): void
    {
        Wrestler::with('names')->get()->each(static function ($wrestler) {
            // Only add if no primary already exists
            if ($wrestler->names()->where('is_primary', true)->doesntExist()) {
                $wrestler->names()->create([
                    'id' => (string) Str::uuid(),
                    'name' => $wrestler->ring_name,
                    'is_primary' => true,
                    'started_at' => $wrestler->debut_date,
                ]);
            }
        });
    }
}
