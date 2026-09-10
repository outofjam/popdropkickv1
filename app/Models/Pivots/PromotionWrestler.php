<?php

namespace App\Models\Pivots;

use App\Models\Promotion;
use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PromotionWrestler extends Pivot
{
    use Blameable;

    protected $table = 'promotion_wrestler';

    protected $fillable = [
        'promotion_id',
        'wrestler_id',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saved(static function (self $pivot) {
            Promotion::forgetCache($pivot->promotion_id);
        });

        static::deleted(static function (self $pivot) {
            Promotion::forgetCache($pivot->promotion_id);
        });
    }
}
