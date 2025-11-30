<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContextValueResource\Pages;
use App\Models\ContextValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContextValueResource extends Resource
{
    protected static ?string $model = ContextValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('context_id')
                    ->relationship('context', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('profile_attribute_id')
                    ->relationship('attribute', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Forms\Components\Textarea::make('value')
                    ->required()
                    ->columnSpanFull()
                    ->rules(function (Forms\Get $get) {
                        $attributeId = $get('profile_attribute_id');
                        if (!$attributeId) {
                            return [];
                        }

                        $attribute = \App\Models\ProfileAttribute::find($attributeId);
                        if (!$attribute) {
                            return [];
                        }

                        // Phone validation for Egyptian numbers: +20 followed by 10 digits
                        if ($attribute->key === 'phone') {
                            return ['regex:/^\+20\s?\d{2,3}\s?\d{3}\s?\d{4}$/'];
                        }

                        return match ($attribute->data_type) {
                            'email' => ['email:rfc'],
                            'url' => ['url'],
                            default => [],
                        };
                    })
                    ->helperText(function (Forms\Get $get) {
                        $attributeId = $get('profile_attribute_id');
                        if (!$attributeId) {
                            return null;
                        }

                        $attribute = \App\Models\ProfileAttribute::find($attributeId);
                        if (!$attribute) {
                            return null;
                        }

                        return match ($attribute->data_type) {
                            'email' => 'Must be a valid email address (RFC compliant)',
                            'url' => 'Must be a valid URL',
                            'string' => $attribute->key === 'phone' ? 'Format: +20 XXX XXX XXXX (Egyptian mobile)' : null,
                            default => null,
                        };
                    }),
                Forms\Components\Select::make('visibility')
                    ->options([
                        'public' => 'Public',
                        'protected' => 'Protected',
                        'private' => 'Private',
                    ])
                    ->default('private') // Privacy by Design: new attributes default to private
                    ->required(),
                Forms\Components\TextInput::make('locale')
                    ->label('Locale')
                    ->default('en')
                    ->maxLength(10)
                    ->helperText('Language/locale code (e.g., en, ar, fr, de)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('context.name')
                    ->label('Context')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('visibility')
                    ->colors([
                        'success' => 'public',
                        'warning' => 'protected',
                        'danger' => 'private',
                    ]),
                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->sortable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        'public' => 'Public',
                        'protected' => 'Protected',
                        'private' => 'Private',
                    ]),
                Tables\Filters\SelectFilter::make('context')
                    ->relationship('context', 'name'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContextValues::route('/'),
            'create' => Pages\CreateContextValue::route('/create'),
            'edit' => Pages\EditContextValue::route('/{record}/edit'),
        ];
    }
}
