<?php

namespace App\Filament\Resources\ContextValueResource\Pages;

use App\Filament\Resources\ContextValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContextValues extends ListRecords
{
    protected static string $resource = ContextValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
