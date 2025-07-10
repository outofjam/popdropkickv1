<?php

namespace App\Models\Pivots;

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
}
