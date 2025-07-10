<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TracksCreatedAndUpdated
{
    public static function bootTracksCreatedAndUpdated(): void
    {
        static::creating(static function ($model) {
            if (Auth::check()) {
                $model->created_by ??= Auth::id();
                $model->updated_by ??= Auth::id();
            }
        });

        static::updating(static function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
