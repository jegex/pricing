<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->decimal('exchange_rate', 20, 10);
            $table->integer('decimal_places')->default(2);
            $table->boolean('enabled')->default(false)->index();
            $table->boolean('default')->default(false)->index();
            $table->boolean('sync_prices')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
