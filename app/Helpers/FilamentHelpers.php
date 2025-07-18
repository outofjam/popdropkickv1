<?php

namespace App\Helpers;

use Filament\Forms\Components\TextInput;

class FilamentHelper
{
    public static function userDisplayField(string $relationship, string $label): TextInput
    {
        return TextInput::make("{$relationship}_name")
            ->label($label)
            ->disabled()
            ->dehydrated(false)
            ->formatStateUsing(static function ($record) use ($relationship) {
                $relationshipMethod = str_replace('_', '', ucwords($relationship, '_'));
                $relationshipMethod = lcfirst($relationshipMethod);
                return $record?->{$relationshipMethod}?->name ?? 'N/A';
            });
    }
}
