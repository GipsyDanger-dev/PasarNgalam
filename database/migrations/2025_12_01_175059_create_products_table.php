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
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            
            // --- TAMBAHAN BARU ---
            $table->string('category')->default('Makanan Berat'); // Kolom Kategori
            $table->json('addons')->nullable(); // Kolom Addons (JSON)
            // ---------------------

            $table->timestamps();
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