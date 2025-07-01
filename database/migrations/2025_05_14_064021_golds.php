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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('address');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('karats', function (Blueprint $table) {
            $table->id();
            $table->string('karat');
            $table->decimal('rate', 5, 2);
            $table->bigInteger('buy_price');
            $table->bigInteger('sell_price');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->foreignId('karat_id')->constrained('karats')->onDelete('cascade');
            $table->decimal('weight', 10, 2);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('transaction_type', ['sale', 'purchase', 'pawning', 'change']);
            $table->enum('payment_method', ['cash', 'online'])->default('cash');
            $table->bigInteger('cash')->default(0);
            $table->bigInteger('change')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('service')->default(0);
            $table->bigInteger('total')->default(0);
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->bigInteger('total_payment')->default(0);
            $table->timestamps();
        });

        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->bigInteger('price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->bigInteger('total_payment')->default(0);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->bigInteger('price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::create('pawnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->date('pawn_date');
            $table->bigInteger('estimated_value')->default(0);
            $table->decimal('rate', 5, 2);
            $table->date('due_date');
            $table->bigInteger('cash')->default(0);
            $table->bigInteger('change')->default(0);
            $table->enum('status', ['pending', 'active', 'paid_off'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pawning_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('pawning_id')->constrained('pawnings')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->foreignId('karat_id')->constrained('karats')->onDelete('cascade');
            $table->decimal('weight', 10, 2);
            $table->integer('quantity');
            $table->string('image')->nullable();
            $table->timestamps();
        });


        Schema::create('changes', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->enum('change_type', ['add', 'deduct', 'change_model']);
            $table->bigInteger('total_payment')->default(0);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('change_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_id')->constrained('changes')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('item_type', ['old', 'new']);
            $table->integer('quantity');
            $table->bigInteger('price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pertukaran
        Schema::dropIfExists('change_items');
        Schema::dropIfExists('changes');

        // Schema::dropIfExists('order_services');
        Schema::dropIfExists('pawning_details');
        Schema::dropIfExists('pawnings');
        Schema::dropIfExists('purchase_details');
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('products');
        Schema::dropIfExists('karats');
        Schema::dropIfExists('types');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('customers');
    }
};
