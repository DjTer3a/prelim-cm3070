<?php

namespace App\Filament\Resources\ContextValueResource\Pages;

use App\Filament\Resources\ContextValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContextValue extends EditRecord
{
    protected static string $resource = ContextValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
