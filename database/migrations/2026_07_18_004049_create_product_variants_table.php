<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// modify by claude
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('packaging_type_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->decimal('retail_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->unsignedInteger('wholesale_min_qty')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['product_id', 'unit_id', 'packaging_type_id'],
                'product_variants_product_unit_packaging_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
