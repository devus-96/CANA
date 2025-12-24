<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Identifiant
            $table->id();

            // Informations de base
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();

            // Prix
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();

            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->enum('stock_status', ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'])->default('IN_STOCK');
            $table->boolean('manage_stock')->default(true);

            // Catégorisation
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Médias
            $table->string('main_image')->nullable();
            $table->json('images')->nullable();

            // Attributs
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_digital')->default(false);

            // Livraison
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('requires_shipping')->default(true);

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();

            // Statistiques
            $table->integer('sales_count')->default(0);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('category_id');
            $table->index('slug');
            $table->index('is_active');
            $table->index(['is_active', 'is_featured']);
            $table->index('stock_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
