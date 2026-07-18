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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_category_id')->constrained()->restrictOnDelete();
            $table->string('name_fr');
            $table->string('name_en');
            $table->enum('origin', ['local', 'imported'])->default('local');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
