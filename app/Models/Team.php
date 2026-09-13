<?php

namespace App\Models;

use App\Traits\TracksCreatedAndUpdated;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A lightweight, reusable label for a group of wrestlers who co-hold a
 * title reign together (tag team, trios, stable). Optional - a reign
 * doesn't need a team_id just because it has multiple participants.
 */
class Team extends Model
{
    use HasFactory;
    use HasUuids;
    use TracksCreatedAndUpdated;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
    ];

    public function titleReigns(): HasMany
    {
        return $this->hasMany(TitleReign::class);
    }
}
