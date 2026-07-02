<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->integer('stock')->default(0);
            $table->integer('stock_available')->default(0);
            $table->string('location', 100)->nullable();
            $table->enum('condition', ['good', 'lightly_damaged', 'heavily_damaged'])->default('good');
            $table->string('image', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
