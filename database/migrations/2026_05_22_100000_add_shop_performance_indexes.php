<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['available', 'category_id']);
            $table->index('category_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->index(['cart_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['available', 'category_id']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['cart_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
