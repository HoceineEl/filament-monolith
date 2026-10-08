<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Products;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use Workbench\App\Filament\Resources\Products\Pages\ManageProducts;
use Workbench\App\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->columnSpanFull(),
            TextInput::make('sku')->label('SKU')->required(),
            Select::make('category')
                ->options(static::categories())
                ->required()
                ->native(false),
            TextInput::make('price')
                ->numeric()
                ->prefix('$')
                ->required()
                ->formatStateUsing(fn (?int $state): ?float => $state === null ? null : $state / 100)
                ->dehydrateStateUsing(fn (float|int|string $state): int => (int) round((float) $state * 100)),
            TextInput::make('stock')->numeric()->required(),
            Toggle::make('is_active')->label('Active')->columnSpanFull(),
            Textarea::make('description')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('sku')->label('SKU')->fontFamily('mono')->color('gray'),
                TextColumn::make('category')->badge()->color('gray'),
                TextColumn::make('price')->money('USD', divideBy: 100)->alignEnd(),
                TextColumn::make('stock')->numeric()->alignEnd()->color(fn (int $state): string => $state === 0 ? 'danger' : 'gray'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->options(static::categories()),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function categories(): array
    {
        $categories = ['Decor', 'Furniture', 'Kitchen', 'Lighting', 'Textiles'];

        return array_combine($categories, $categories);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
