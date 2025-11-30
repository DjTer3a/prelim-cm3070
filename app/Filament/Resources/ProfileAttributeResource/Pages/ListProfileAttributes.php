<?php

namespace App\Filament\Resources\ProfileAttributeResource\Pages;

use App\Filament\Resources\ProfileAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfileAttributes extends ListRecords
{
    protected static string $resource = ProfileAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
