<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One wrestler's participation in a title reign, with their own
 * alias-at-win. A reign with N participants (tag team, trios, stable)
 * has N of these rows; a singles reign has exactly one.
 *
 * Currently kept in sync automatically from TitleReign::wrestler_id /
 * wrestler_name_id_at_win (see TitleReign::syncPrimaryParticipant()) -
 * those columns are still the write path's source of truth until
 * services/requests are updated to write participants directly.
 */
class TitleReignWrestler extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title_reign_id',
        'wrestler_id',
        'wrestler_name_id_at_win',
    ];

    public function titleReign(): BelongsTo
    {
        return $this->belongsTo(TitleReign::class);
    }

    public function wrestler(): BelongsTo
    {
        return $this->belongsTo(Wrestler::class);
    }

    public function aliasAtWin(): BelongsTo
    {
        return $this->belongsTo(WrestlerName::class, 'wrestler_name_id_at_win');
    }
}
