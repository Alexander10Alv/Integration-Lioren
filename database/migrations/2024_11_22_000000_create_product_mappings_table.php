<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_product_id')->unique();
            $table->string('lioren_product_id')->nullable();
            $table->string('product_title');
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->enum('sync_status', ['pending', 'synced', 'error'])->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_topic');
            $table->string('shopify_id')->nullable();
            $table->enum('status', ['received', 'processed', 'error'])->default('received');
            $table->text('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('product_mappings');
    }
};
