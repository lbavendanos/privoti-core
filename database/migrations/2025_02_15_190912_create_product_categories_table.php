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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('rank')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
