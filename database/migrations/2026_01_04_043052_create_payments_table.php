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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('flow_token')->nullable();
            $table->string('subject');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('CLP');
            $table->string('email');
            $table->integer('payment_method');
            $table->integer('status')->default(0); // 0=created, 1=pending, 2=paid, 3=rejected, 4=cancelled
            $table->json('flow_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'created_at']);
            $table->index('flow_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
