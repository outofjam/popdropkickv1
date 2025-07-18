<?php

namespace App\Helpers;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class FilamentHelpers
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

    public static function userDisplayColumn(string $relationship, string $label): TextColumn
    {
        $relationshipMethod = str_replace('_', '', ucwords($relationship, '_'));
        $relationshipMethod = lcfirst($relationshipMethod);

        return TextColumn::make("{$relationshipMethod}.name")
            ->label($label)
            ->searchable()
            ->sortable();
    }
}
