<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileAttributeResource\Pages;
use App\Filament\Resources\ProfileAttributeResource\RelationManagers;
use App\Models\ProfileAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfileAttributeResource extends Resource
{
    protected static ?string $model = ProfileAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('data_type')
                    ->required(),
                Forms\Components\TextInput::make('schema_type'),
                Forms\Components\Toggle::make('is_system')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schema_type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_system')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfileAttributes::route('/'),
            'create' => Pages\CreateProfileAttribute::route('/create'),
            'edit' => Pages\EditProfileAttribute::route('/{record}/edit'),
        ];
    }
}
