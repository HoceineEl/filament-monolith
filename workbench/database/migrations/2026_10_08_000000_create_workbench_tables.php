<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->string('customer');
            $table->string('email');
            $table->string('city');
            $table->string('status');
            $table->unsignedInteger('total');
            $table->string('shipping_method');
            $table->boolean('is_paid')->default(false);
            $table->date('placed_at');
            $table->json('items')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category');
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
    }
};
