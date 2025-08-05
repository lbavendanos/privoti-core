<?php

use App\Models\Product;
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
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('handle');
            $table->string('description')->nullable();
            $table->enum('status', Product::STATUS_LIST)->default(Product::STATUS_DEFAULT);
            $table->string('tags')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('product_categories');
            $table->foreignId('type_id')->nullable()->constrained('product_types');
            $table->foreignId('vendor_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
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
