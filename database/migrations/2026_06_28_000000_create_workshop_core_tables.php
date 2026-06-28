<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->string('phone')->nullable();
            $table->string('postal_code', 8)->nullable();
            $table->string('address')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('complement', 80)->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cnpj')->unique();
            $table->string('phone')->nullable();
            $table->string('postal_code', 8)->nullable();
            $table->string('address')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('complement', 80)->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();
            $table->string('website_url')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('model');
            $table->string('brand');
            $table->string('plate')->unique();
            $table->char('year', 4)->nullable();
            $table->unsignedInteger('current_km')->nullable();
            $table->string('color')->nullable();
            $table->string('fuel_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('name');
            $table->decimal('price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->string('photo_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('Aberto');
            $table->text('observation')->nullable();
            $table->decimal('service_total', 12, 2)->default(0);
            $table->decimal('items_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('services');
        Schema::dropIfExists('providers');
        Schema::dropIfExists('clients');
    }
};
