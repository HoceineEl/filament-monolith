<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Enums\OrderStatus;
use Workbench\App\Models\Order;
use Workbench\App\Models\Product;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create(['name' => 'Ada Lovelace', 'email' => 'ada@northwind.test', 'password' => bcrypt('password')]);

        $products = collect([
            ['Aero Desk Lamp', 'LMP-001', 'Lighting', 8900, 42],
            ['Basalt Mug', 'MUG-014', 'Kitchen', 1800, 230],
            ['Cirrus Throw Blanket', 'TXT-203', 'Textiles', 6400, 18],
            ['Drift Wall Clock', 'CLK-077', 'Decor', 5200, 0],
            ['Ember Candle Set', 'CND-310', 'Decor', 2900, 96],
            ['Fjord Side Table', 'FRN-122', 'Furniture', 21900, 7],
        ])->map(fn (array $product, int $index): Product => Product::query()->create([
            'name' => $product[0],
            'sku' => $product[1],
            'category' => $product[2],
            'price' => $product[3],
            'stock' => $product[4],
            'is_active' => $index !== 3,
            'description' => "{$product[0]} from the spring collection.",
        ]));

        collect([
            ['Acme Corporation', 'hello@acme.test', 'Casablanca', OrderStatus::Delivered, 'express', true],
            ['Blue Harbor Studio', 'team@blueharbor.test', 'Lisbon', OrderStatus::Shipped, 'standard', true],
            ['Cedar & Co', 'contact@cedar.test', 'Beirut', OrderStatus::Processing, 'standard', true],
            ['Dune Logistics', 'ops@dune.test', 'Marrakech', OrderStatus::Pending, 'pickup', false],
            ['Evergreen Clinic', 'care@evergreen.test', 'Lyon', OrderStatus::Cancelled, 'express', false],
            ['Falcon Air', 'fly@falcon.test', 'Dubai', OrderStatus::Delivered, 'express', true],
            ['Granite Labs', 'lab@granite.test', 'Berlin', OrderStatus::Processing, 'standard', true],
            ['Harbor Bakery', 'bread@harbor.test', 'Porto', OrderStatus::Shipped, 'standard', true],
            ['Indigo Health', 'info@indigo.test', 'Amman', OrderStatus::Pending, 'pickup', false],
            ['Juniper Books', 'read@juniper.test', 'Tunis', OrderStatus::Delivered, 'standard', true],
        ])->each(function (array $order, int $index) use ($products): void {
            $items = collect([$products[$index % 6], $products[($index + 2) % 6]])
                ->map(fn (Product $product, int $line): array => ['product' => $product->name, 'quantity' => $line + 1 + ($index % 3), 'price' => $product->price / 100]);

            Order::query()->create([
                'number' => sprintf('NW-%05d', 10240 + $index),
                'customer' => $order[0],
                'email' => $order[1],
                'city' => $order[2],
                'status' => $order[3],
                'shipping_method' => $order[4],
                'is_paid' => $order[5],
                'total' => (int) round($items->sum(fn (array $item): float => $item['quantity'] * $item['price']) * 100),
                'placed_at' => sprintf('2026-03-%02d', 28 - ($index * 2)),
                'items' => $items->all(),
                'notes' => $index === 0 ? 'Leave the parcel at the front desk.' : null,
            ]);
        });
    }
}
