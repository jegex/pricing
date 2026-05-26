<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_id')->nullable()->index();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->string('priceable_type')->nullable();
            $table->unsignedBigInteger('priceable_id')->nullable();
            $table->index(['priceable_type', 'priceable_id']);
            $table->unsignedBigInteger('price')->index();
            $table->unsignedBigInteger('compare_price')->nullable();
            $table->integer('min_quantity')->default(1)->index();
            $table->unique(['currency_id', 'priceable_type', 'priceable_id', 'customer_group_id', 'min_quantity'], 'prices_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
