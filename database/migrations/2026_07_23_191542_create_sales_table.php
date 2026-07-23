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
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total_usd', 12, 2);
            // Fige le taux et le total CDF au moment de la vente : une vente
            // passée ne doit jamais changer de valeur si le taux change ensuite.
            $table->decimal('exchange_rate', 12, 4);
            $table->decimal('total_cdf', 14, 2);
            $table->enum('payment_currency', ['usd', 'cdf']);
            $table->decimal('amount_tendered', 12, 2);
            $table->decimal('change_due', 12, 2);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
