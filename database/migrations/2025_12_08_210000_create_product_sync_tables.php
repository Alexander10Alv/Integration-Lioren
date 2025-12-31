<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla de mapeo de productos entre plataformas
        Schema::create('product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Identificadores de plataformas
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_variant_id')->nullable();
            $table->string('lioren_product_id')->nullable();
            $table->string('sku')->index(); // SKU es el identificador común
            
            // Datos del producto (cache)
            $table->string('product_title');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            
            // Estado de sincronización
            $table->enum('sync_status', ['synced', 'pending', 'error'])->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['user_id', 'sku']);
            $table->index(['shopify_product_id']);
            $table->index(['lioren_product_id']);
        });

        // Tabla de configuración de bodegas/locations
        Schema::create('warehouse_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Modo de sincronización
            $table->enum('sync_mode', ['simple', 'advanced'])->default('simple');
            
            // Bodega por defecto (fallback)
            $table->integer('default_bodega_id')->nullable();
            $table->string('default_bodega_name')->nullable();
            
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Tabla de mapeo location → bodega
        Schema::create('location_bodega_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Shopify location
            $table->string('shopify_location_id');
            $table->string('shopify_location_name');
            
            // Lioren bodega
            $table->integer('lioren_bodega_id');
            $table->string('lioren_bodega_name');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'shopify_location_id']);
        });

        // Tabla de locations pendientes de mapear
        Schema::create('pending_location_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('shopify_location_id');
            $table->string('shopify_location_name');
            
            // Contador de productos afectados
            $table->integer('affected_products_count')->default(0);
            
            // Estado
            $table->enum('status', ['pending', 'notified', 'resolved'])->default('pending');
            $table->timestamp('first_detected_at');
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'shopify_location_id']);
        });

        // Tabla de logs de sincronización
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de sincronización
            $table->enum('sync_type', ['initial', 'webhook', 'scheduled', 'manual'])->default('webhook');
            $table->enum('direction', ['shopify_to_lioren', 'lioren_to_shopify', 'bidirectional']);
            
            // Entidad afectada
            $table->enum('entity_type', ['product', 'inventory', 'order']);
            $table->string('entity_id')->nullable();
            $table->string('sku')->nullable();
            
            // Resultado
            $table->enum('status', ['success', 'error', 'retry'])->default('success');
            $table->text('message')->nullable();
            $table->json('data')->nullable(); // Datos adicionales para debugging
            
            // Reintentos
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'next_retry_at']);
        });

        // Tabla de trabajos de sincronización pendientes (para reintentos)
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de operación
            $table->enum('operation', ['create', 'update', 'delete', 'sync_inventory']);
            $table->enum('platform', ['shopify', 'lioren']);
            
            // Datos de la operación
            $table->json('payload');
            $table->string('sku')->nullable();
            
            // Estado
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->text('last_error')->nullable();
            
            // Timestamps
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sync_queue');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('pending_location_mappings');
        Schema::dropIfExists('location_bodega_mappings');
        Schema::dropIfExists('warehouse_mappings');
        Schema::dropIfExists('product_mappings');
    }
};
