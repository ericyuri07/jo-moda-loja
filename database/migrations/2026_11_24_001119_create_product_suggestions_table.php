<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Produtos que o admin escolhe para aparecer no "Veja também" de outro.
     */
    public function up(): void
    {
        Schema::create('product_suggestions', function (Blueprint $table) {
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('suggested_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedTinyInteger('position')->default(0);
            $table->primary(['product_id', 'suggested_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_suggestions');
    }
};
