<?php

namespace App\Models\Pivots;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActivePromotionWrestler extends Pivot
{
    use Blameable;

    protected $table = 'active_promotion_wrestler';

    protected $fillable = [
        'promotion_id',
        'wrestler_id',
        'created_by',
        'updated_by',
    ];
}
