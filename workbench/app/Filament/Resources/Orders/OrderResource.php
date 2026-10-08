<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Orders;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Filament\Resources\Orders\Pages\CreateOrder;
use Workbench\App\Filament\Resources\Orders\Pages\EditOrder;
use Workbench\App\Filament\Resources\Orders\Pages\ListOrders;
use Workbench\App\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order details')
                ->description('Customer, status and payment.')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('number')->required(),
                    TextInput::make('customer')->required(),
                    TextInput::make('email')->email()->required(),
                    Select::make('status')->options(OrderStatus::class)->required()->native(false),
                    DatePicker::make('placed_at')->required()->native(false),
                    Toggle::make('is_paid')->label('Paid')->inline(false),
                ]),
            Tabs::make('Fulfilment')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Items')->icon(Heroicon::OutlinedListBullet)->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->columns(3)
                            ->schema([
                                TextInput::make('product')->required(),
                                TextInput::make('quantity')->numeric()->required(),
                                TextInput::make('price')->numeric()->prefix('$')->required(),
                            ]),
                    ]),
                    Tab::make('Shipping')->icon(Heroicon::OutlinedTruck)->columns(2)->schema([
                        Select::make('shipping_method')
                            ->options(['standard' => 'Standard', 'express' => 'Express', 'pickup' => 'Store pickup'])
                            ->required()
                            ->native(false),
                        TextInput::make('city')->required(),
                    ]),
                    Tab::make('Notes')->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)->schema([
                        Textarea::make('notes')->rows(4),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('number')->searchable()->sortable()->weight('medium'),
                TextColumn::make('customer')->searchable()->description(fn (Order $record): string => $record->email),
                TextColumn::make('city'),
                TextColumn::make('status')->badge(),
                TextColumn::make('shipping_method')->label('Shipping')->badge()->color('gray'),
                TextColumn::make('total')->money('USD', divideBy: 100)->sortable()->alignEnd(),
                IconColumn::make('is_paid')->label('Paid')->boolean(),
                TextColumn::make('placed_at')->label('Placed')->date('M j, Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class),
                TernaryFilter::make('is_paid')->label('Paid'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('markShipped')
                        ->label('Mark as shipped')
                        ->icon(Heroicon::OutlinedTruck)
                        ->requiresConfirmation()
                        ->modalDescription('The customer will receive a tracking email.')
                        ->action(fn (Order $record) => $record->update(['status' => OrderStatus::Shipped])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
